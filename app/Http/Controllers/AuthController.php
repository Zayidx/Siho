<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // ... method lain yang mungkin sudah ada

    /**
     * Menangani proses logout pengguna.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
        $request->session()->flash('success', 'Anda telah berhasil logout.'); // Tampilkan pesan sukses();
        return view('auth.login'); // Arahkan ke halaman utama/login
    }
}