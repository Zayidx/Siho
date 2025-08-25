<?php

// app/Livewire/Auth/Login.php

namespace App\Livewire\Auth;

use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Login extends Component
{
    #[Validate('required|email')]
    public $email;

    #[Validate('required')]
    public $password;

    /**
     * Cek jika user sudah login saat komponen di-mount.
     * Jika sudah, redirect ke dashboard sesuai role.
     */
    public function mount()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role) {
                $roleName = $user->role->name;
                
                if ($roleName === 'admin' || $roleName === 'superadmin') {
                   return $this->redirect(route('admin.dashboard'), navigate: true);
                } elseif ($roleName === 'staffhubin') {
                   return $this->redirect(route('staffhubin.dashboard'), navigate: true);
                } elseif ($roleName === 'user') {
                    return $this->redirect(route('user.dashboard'), navigate: true);
                }
            }
            
            // Jika user login tapi tidak punya role yang valid, logout paksa.
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }
    }

    #[Title('Halaman Login')]
    #[Layout('components.layouts.layout-auth')]
    public function render()
    {
        return view('livewire.auth.login');
    }

    /**
     * Mencoba untuk login user.
     * Jika berhasil, langsung redirect ke dashboard.
     */
    public function attemptLogin()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = ['email' => $this->email, 'password' => $this->password];
        $user = User::where('email', $this->email)->first();

        // Cek kredensial dan pastikan user memiliki role yang diizinkan.
        if ($user && Auth::validate($credentials)) {
            if ($user->role && in_array($user->role->name, ['admin', 'superadmin', 'user', 'staffhubin'])) {
                
                // Langsung login user
                Auth::login($user);
                request()->session()->regenerate();

                // Set session untuk sweet alert
                $roleName = $user->role->name;
                $roleDisplayName = $this->getRoleDisplayName($roleName);
                request()->session()->flash('login_success', [
                    'message' => "Berhasil login sebagai $roleDisplayName",
                    'role' => $roleName
                ]);

                // Redirect berdasarkan role
                if ($roleName === 'admin' || $roleName === 'superadmin') {
                    return $this->redirect(route('admin.dashboard'), navigate: true);
                } elseif ($roleName === 'staffhubin') {
                    return $this->redirect(route('staffhubin.dashboard'), navigate: true);
                } elseif ($roleName === 'user') {
                    return $this->redirect(route('user.dashboard'), navigate: true);
                }
            } else {
                $this->addError('credentials', 'Anda tidak memiliki hak akses untuk masuk.');
            }
        } else {
            $this->addError('credentials', 'Gagal masuk, email atau password salah!');
        }
    }

    /**
     * Get display name untuk role
     */
    private function getRoleDisplayName($roleName)
    {
        return match($roleName) {
            'admin', 'superadmin' => 'Administrator',
            'staffhubin' => 'Staff Hubin',
            'user' => 'Siswa',
            default => 'User'
        };
    }

    /**
     * Logout user.
     */
    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return $this->redirect('/', navigate: true);
    }
}
