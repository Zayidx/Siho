<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;

// Atur layout default untuk komponen ini
class UserManagement extends Component
{
    use WithFileUploads, WithPagination;
    protected $paginationTheme = 'bootstrap';
    
    #[Title("Manajemen User")]
    public $isModalOpen = false;
    public $userId, $username, $email, $password, $password_confirmation, $foto, $existingFoto;
    public $role_id = null; // Ganti dari roles_id agar konsisten
    public $roles;
    public $search = '';
    public $perPage = 10;

    protected function rules()
    {
        // Aturan validasi dinamis
        $passwordRule = $this->userId ? 'nullable|string|min:8|confirmed' : 'required|string|min:8|confirmed';
        $fotoRule = $this->userId ? 'nullable|image|max:2048' : 'required|image|max:2048';

        return [
            'username' => ['required', 'string', 'max:60', Rule::unique('users')->ignore($this->userId)],
            'email' => ['required', 'string', 'email', 'max:60', Rule::unique('users')->ignore($this->userId)],
            'password' => $passwordRule,
            'password_confirmation' => $this->userId ? 'nullable' : 'required_with:password',
            'foto' => $fotoRule,
            'role_id' => 'required|exists:roles,id', // Ganti dari roles_id
        ];
    }

    protected function messages()
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role_id.required' => 'Peran (role) wajib dipilih.',
            'foto.required' => 'Foto profil wajib diunggah.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }

    public function mount()
    {
        $this->roles = Role::all();
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $searchTerm = '%' . $this->search . '%';
        $users = User::with('role')
            ->where(function ($query) use ($searchTerm) {
                $query->where('username', 'like', $searchTerm)
                      ->orWhere('email', 'like', $searchTerm);
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.user-management', [
            'users' => $users
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $id;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->existingFoto = $user->foto ? Storage::url($user->foto) : null;
        $this->role_id = $user->role_id;
        $this->isModalOpen = true;
    }

    public function store()
    {
        $validatedData = $this->validate();

        $data = [
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'role_id' => $validatedData['role_id'],
        ];

        if (!empty($validatedData['password'])) {
            $data['password'] = Hash::make($validatedData['password']);
        }

        if ($this->foto) {
            if ($this->userId && $this->existingFoto) {
                $oldPath = str_replace('/storage/', '', $this->existingFoto);
                Storage::disk('public')->delete($oldPath);
            }
            $data['foto'] = $this->foto->store('fotos', 'public');
        }

        User::updateOrCreate(['id' => $this->userId], $data);
        
        $this->dispatch('swal:success', [
            'message' => $this->userId ? 'Data user berhasil diperbarui.' : 'User baru berhasil ditambahkan.'
        ]);

        $this->closeModal();
    }

    #[On('destroy')]
    public function destroy($id)
    {
        if ($id == auth()->id()) {
            $this->dispatch('swal:error', ['message' => 'Aksi tidak diizinkan. Anda tidak dapat menghapus akun Anda sendiri.']);
            return;
        }

        $user = User::findOrFail($id);
        if ($user->foto) {
            Storage::disk('public')->delete($user->foto);
        }
        $user->delete();

        $this->dispatch('swal:success', ['message' => 'Data user berhasil dihapus.']);
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['userId', 'username', 'email', 'password', 'password_confirmation', 'foto', 'existingFoto', 'role_id']);
        $this->resetErrorBag();
    }
}
