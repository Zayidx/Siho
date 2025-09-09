<?php

namespace App\Livewire\Auth;

use App\Mail\RegistrationOtpMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Support\Uploads\Uploader;

#[Layout("components.layouts.layout-auth")]
#[Title("Halaman Registrasi Pengguna")]
class Register extends Component
{
    use WithFileUploads;

    public $email, $username, $password, $password_confirmation, $foto;
    public $full_name, $phone, $address;
    // New address fields
    public $province, $province_id, $city, $city_id, $street, $rt, $rw;
    public $devOtp = null;
    public $mailSent = false;
    public $mailError = null;
    public $otp;
    public $showOtpForm = false;

    protected function rules()
    {
        return [
            'full_name' => 'required|string|min:3|max:150',
            'username' => 'required|string|min:4|max:100|alpha_dash|unique:users,username',
            'email' => 'required|email|max:100|unique:users,email',
            'phone' => 'required|string|min:8|max:20',
            // Split address rules
            'province' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'street' => 'required|string|min:3|max:200',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'password' => 'required|string|min:6|confirmed',
            'foto' => 'nullable|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    protected $messages = [
        'email.unique' => 'Email ini sudah terdaftar.',
        'username.required' => 'Nama pengguna wajib diisi.',
        'password.required' => 'Password wajib diisi.',
        'password.min' => 'Password minimal 6 karakter.',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
        'full_name.required' => 'Nama lengkap wajib diisi.',
        'username.required' => 'Nama pengguna wajib diisi.',
        'phone.required' => 'Nomor telepon wajib diisi.',
        'province.required' => 'Provinsi wajib dipilih.',
        'city.required' => 'Kota/Kabupaten wajib dipilih.',
        'street.required' => 'Nama jalan/rumah wajib diisi.',
    ];

    public function render()
    {
        return view('livewire.auth.register');
    }

    public function submitRegistrationDetails()
    {
        $this->validate();
        $this->sendOtp();
    }

    public function sendOtp()
    {
        $otpCode = rand(100000, 999999);
        $otpKey = "otp_register:" . $this->email;
        Cache::put($otpKey, $otpCode, now()->addMinutes(5)); // OTP berlaku 5 menit

        $this->mailSent = false;
        $this->mailError = null;
        $attempts = 0;
        $maxAttempts = 2;
        while ($attempts < $maxAttempts) {
            try {
                Mail::to($this->email)->send(new RegistrationOtpMail($otpCode));
                $this->mailSent = true;
                break;
            } catch (\Exception $e) {
                $this->mailError = $e->getMessage();
                report($e);
                $attempts++;
            }
        }
        if ($this->mailSent) {
            $this->showOtpForm = true;
            $this->resetErrorBag();
            $this->dispatch('otp-sent');
            if (app()->environment(['local', 'testing']) || config('app.debug')) {
                $this->devOtp = (string) $otpCode;
            }
        } else {
            if (app()->environment(['local', 'testing']) || config('app.debug')) {
                $this->devOtp = (string) $otpCode;
                $this->showOtpForm = true;
                $this->dispatch('otp-sent');
            } else {
                $this->addError('credentials', 'Gagal mengirim OTP. Coba lagi nanti atau hubungi admin.');
            }
        }
    }

    public function resendOtp()
    {
        $this->sendOtp();
    }

    public function verifyOtpAndCreateUser()
    {
        $this->validate(['otp' => 'required|numeric|digits:6']);
        $otpKey = "otp_register:" . $this->email;
        $storedOtp = Cache::get($otpKey);

        if ($storedOtp && $storedOtp == $this->otp) {
            try {
                // Menggunakan DB::transaction untuk memastikan konsistensi.
                $user = DB::transaction(function () use ($otpKey) {
                    // 1. Cari atau buat Role 'users' jika belum ada
                    $userRole = Role::firstOrCreate(['name' => 'users']);

                    // 2. Simpan foto jika ada
                    $fotoPath = null;
                    if ($this->foto) {
                        $fotoPath = Uploader::storePublicImage($this->foto, 'fotos');
                    }

                    // 3. Buat data di tabel 'users'.
                    // Compose full address from parts
                    $addrParts = array_filter([
                        trim((string) $this->street),
                        trim((string) ($this->rt ? ('RT ' . $this->rt) : '')),
                        trim((string) ($this->rw ? ('RW ' . $this->rw) : '')),
                    ]);
                    $fullAddress = implode(', ', $addrParts);

                    $newUser = User::create([
                        'role_id' => $userRole->id,
                        'username' => $this->username,
                        'full_name' => $this->full_name,
                        'email' => $this->email,
                        'phone' => $this->phone,
                        'address' => $fullAddress,
                        'city' => $this->city,
                        'province' => $this->province,
                        'password' => Hash::make($this->password),
                        'email_verified_at' => now(),
                        'foto' => $fotoPath,
                    ]);

                    // 4. Hapus OTP dari Redis setelah berhasil digunakan.
                    Cache::forget($otpKey);

                    // 5. Kembalikan user yang baru dibuat untuk proses login.
                    return $newUser;
                });

                // Setelah transaksi berhasil, loginkan user.
                Auth::login($user);
                request()->session()->regenerate();

                // Redirect ke dashboard pengguna setelah registrasi
                return $this->redirect(route('user.dashboard'), navigate: true);

            } catch (\Exception $e) {
                // Jika terjadi error di dalam transaksi, tampilkan pesan.
                $this->addError('credentials', 'Gagal membuat akun. Terjadi kesalahan pada server: ' . $e->getMessage());
                report($e); // Laporkan error untuk dianalisis
                return;
            }
        }
        
        $this->addError('otp', 'Kode OTP tidak valid atau telah kedaluwarsa.');
    }

    public function cancelOtp()
    {
        $this->reset([
            'email', 'username', 'full_name', 'phone',
            'province','province_id','city','city_id','street','rt','rw',
            'address', // legacy; kept for compatibility but no longer used in form
            'password', 'password_confirmation', 'foto', 'otp', 'showOtpForm'
        ]);
        $this->resetErrorBag();
    }

    protected $validationAttributes = [
        'full_name' => 'Nama lengkap',
        'username' => 'Nama pengguna',
        'email' => 'Email',
        'phone' => 'No. telepon',
        'province' => 'Provinsi',
        'city' => 'Kota/Kabupaten',
        'street' => 'Alamat jalan/rumah',
        'rt' => 'RT',
        'rw' => 'RW',
        'password' => 'Password',
        'password_confirmation' => 'Konfirmasi password',
        'foto' => 'Foto',
        'otp' => 'Kode OTP',
    ];
}
