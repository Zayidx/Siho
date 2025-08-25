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

#[Layout("components.layouts.layout-auth")]
#[Title("Halaman Registrasi Pengguna")]
class Register extends Component
{
    use WithFileUploads;

    public $email, $username, $password, $password_confirmation, $foto;
    public $devOtp = null;
    public $mailSent = false;
    public $mailError = null;
    public $otp;
    public $showOtpForm = false;

    protected function rules()
    {
        return [
            'email' => 'required|email|max:100|unique:users,email',
            'username' => 'required|string|min:4|max:100|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'foto' => 'required|image|max:2048',
        ];
    }

    protected $messages = [
        'email.unique' => 'Email ini sudah terdaftar.',
        'username.required' => 'Nama pengguna wajib diisi.',
        'password.required' => 'Password wajib diisi.',
        'password.min' => 'Password minimal 6 karakter.',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
        'foto.required' => 'Foto profil wajib diunggah.',
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
                    // 1. Cari Role 'users'. Gagal jika tidak ditemukan.
                    $userRole = Role::where('name', 'users')->firstOrFail();

                    // 2. Simpan foto ke storage dan dapatkan path-nya.
                    $fotoPath = $this->foto->store('fotos', 'public');

                    // 3. Buat data di tabel 'users'.
                    $newUser = User::create([
                        'role_id' => $userRole->id,
                        'username' => $this->username,
                        'full_name' => $this->username,
                        'email' => $this->email,
                        'password' => Hash::make($this->password),
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

                // Redirect setelah login berhasil (pengguna non-admin ke beranda)
                return $this->redirect('/', navigate: true);

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
        $this->reset(['email', 'username', 'password', 'password_confirmation', 'foto', 'otp', 'showOtpForm']);
        $this->resetErrorBag();
    }
}
