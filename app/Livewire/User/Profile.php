<?php

namespace App\Livewire\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use App\Mail\VerifyNewEmailMail;
use Illuminate\Support\Facades\Mail;

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
            'photo' => 'nullable|image|max:2048',
        ], [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
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
            } catch (\Throwable $e) { report($e); }
        }
        if ($this->photo) {
            if ($u->foto) {
                Storage::disk('public')->delete($u->foto);
            }
            $u->foto = $this->photo->store('fotos', 'public');
        }
        if (!empty($data['password'])) {
            $u->password = Hash::make($data['password']);
        }
        $u->save();

        $msg = $verificationSent ? 'Profil diperbarui. Cek email baru untuk verifikasi.' : 'Profil berhasil diperbarui.';
        $this->dispatch('swal:success', ['message' => $msg]);
        $this->reset(['password','password_confirmation']);
    }
}
