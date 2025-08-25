<?php

// =========================================================================================
// FILE 1: Controller User (DIKEMBALIKAN KE VERSI AJAX)
// Path: app/Http/Controllers/UsersController.php
// Perubahan: Semua method mengembalikan JSON untuk mendukung interaksi satu halaman.
// =========================================================================================

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Exception;

class UsersController extends Controller
{
    /**
     * Menampilkan halaman manajemen user atau mengembalikan data user dalam format JSON.
     */
    public function index(Request $request)
    {
        // Jika request adalah AJAX, kembalikan data user untuk diisi ke tabel
        if ($request->ajax()) {
            $data = User::with('role')->latest()->get();
            return response()->json(['data' => $data]);
        }
        
        // Jika bukan AJAX, tampilkan view dengan data roles untuk dropdown di modal
        $roles = Role::all();
        return view('admin.master-admin.user_dashboard', compact('roles'));
    }
    
    /**
     * Menyimpan user baru ke dalam database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:60|unique:users',
            'email' => 'required|string|email|max:60|unique:users',
            'password' => 'required|string|min:8',
            'foto' => ['required', File::image()->max('2mb')],
            'roles_id' => 'required|exists:roles,id',
        ]);

        try {
            DB::transaction(function () use ($validatedData, $request) {
                $path = $request->file('foto')->store('fotos', 'public');
                User::create([
                    'username' => $validatedData['username'],
                    'email' => $validatedData['email'],
                    'password' => $validatedData['password'],
                    'foto' => $path,
                    'roles_id' => $validatedData['roles_id'],
                ]);
            });

            return response()->json(['success' => 'User berhasil ditambahkan.']);

        } catch (Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menambahkan user. Terjadi kesalahan pada server.'], 500);
        }
    }

    /**
     * Mengambil data satu user spesifik untuk form edit di modal.
     */
    public function show(User $user)
    {
        // Tambahkan atribut 'foto_url' ke objek user agar mudah diakses di frontend
        $user->foto_url = $user->foto ? Storage::url($user->foto) : null;
        return response()->json($user);
    }

    /**
     * Memperbarui data user yang sudah ada.
     */
    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'username' => ['required', 'string', 'max:60', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:60', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'foto' => ['nullable', File::image()->max('2mb')],
            'roles_id' => 'required|exists:roles,id',
        ]);

        try {
            DB::transaction(function () use ($validatedData, $request, $user) {
                $updateData = [
                    'username' => $validatedData['username'],
                    'email' => $validatedData['email'],
                    'roles_id' => $validatedData['roles_id'],
                ];
                if (!empty($validatedData['password'])) {
                    $updateData['password'] = $validatedData['password'];
                }
                if ($request->hasFile('foto')) {
                    if ($user->foto) Storage::disk('public')->delete($user->foto);
                    $updateData['foto'] = $request->file('foto')->store('fotos', 'public');
                }
                $user->update($updateData);
            });

            return response()->json(['success' => 'User berhasil diperbarui.']);

        } catch (Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memperbarui user. Terjadi kesalahan pada server.'], 500);
        }
    }

    /**
     * Menghapus user dari database.
     */
    public function destroy(User $user)
    {
        try {
            DB::transaction(function () use ($user) {
                if ($user->foto) Storage::disk('public')->delete($user->foto);
                $user->delete();
            });

            return response()->json(['success' => 'User berhasil dihapus.']);

        } catch (Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menghapus user. Terjadi kesalahan pada server.'], 500);
        }
    }
}
