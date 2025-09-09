<?php

namespace App\Livewire\User;

use App\Mail\VerifyNewEmailMail;
use App\Support\Uploads\Uploader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.user')]
#[Title('Profil Saya')]
class Profile extends Component
{
    use WithFileUploads;

    public $full_name;

    public $phone;

    public $address;

    public $email;

    public $photo; // uploaded photo

    public $password;

    public $password_confirmation;

    public function mount()
    {
        $u = Auth::user();
        $this->full_name = $u->full_name;
        $this->phone = $u->phone;
        $this->address = $u->address;
        $this->email = $u->email;
    }

    public function render()
    {
        return view('livewire.user.profile');
    }

    public function save()
    {
        $data = $this->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'email' => 'required|email|max:160|unique:users,email,'.Auth::id(),
            'password' => 'nullable|string|min:8|confirmed',
            'photo' => 'nullable|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'full_name.string' => 'Nama lengkap tidak valid.',
            'full_name.max' => 'Nama terlalu panjang.',
            'phone.max' => 'Nomor telepon terlalu panjang.',
            'address.max' => 'Alamat terlalu panjang.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'photo.mimes' => 'Format foto harus jpg, jpeg, png, atau webp.',
            'photo.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        $u = Auth::user();
        $u->full_name = $data['full_name'];
        $u->phone = $data['phone'] ?? null;
        $u->address = $data['address'] ?? null;
        $verificationSent = false;
        if ($data['email'] !== $u->email) {
            // simpan pending_email dan kirim link verifikasi bertanda tangan
            $u->pending_email = $data['email'];
            $verificationUrl = URL::temporarySignedRoute(
                'verification.new', now()->addMinutes(60), [
                    'user' => $u->id,
                    'email' => $u->pending_email,
                ]
            );
            try {
                Mail::to($u->pending_email)->queue(new VerifyNewEmailMail($verificationUrl, $u->full_name ?? $u->username));
                $verificationSent = true;
            } catch (\Throwable $e) {
                report($e);
            }
        }
        if ($this->photo) {
            Uploader::deletePublicIfLocal($u->foto);
            $u->foto = Uploader::storePublicImage($this->photo, 'fotos');
        }
        if (! empty($data['password'])) {
            $u->password = Hash::make($data['password']);
        }
        $u->save();

        $msg = $verificationSent ? 'Profil diperbarui. Cek email baru untuk verifikasi.' : 'Profil berhasil diperbarui.';
        $this->dispatch('swal:success', ['message' => $msg]);
        $this->reset(['password', 'password_confirmation']);
    }

    protected $validationAttributes = [
        'full_name' => 'Nama lengkap',
        'phone' => 'No. telepon',
        'address' => 'Alamat',
        'email' => 'Email',
        'password' => 'Password',
        'photo' => 'Foto profil',
    ];
}
