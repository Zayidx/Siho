<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use App\Models\User;
use App\Support\Uploads\Uploader;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

// Atur layout default untuk komponen ini
#[Layout('components.layouts.app')]
class UserManagement extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Title('Manajemen User')]
    public $isModalOpen = false;

    public $userId;

    public $username;

    public $email;

    public $password;

    public $password_confirmation;

    public $foto;

    public $existingFoto;

    public $role_id = null; // Ganti dari roles_id agar konsisten

    public $roles;

    public $search = '';

    public $perPage = 10;

    // First-party data fields
    public $full_name = '';

    public $phone = '';

    public $address = '';

    public $city = '';

    public $province = '';

    public $date_of_birth = '';

    public $gender = '';

    public $stay_purpose = '';

    protected function rules()
    {
        // Aturan validasi dinamis
        $passwordRule = $this->userId ? 'nullable|string|min:8|confirmed' : 'required|string|min:8|confirmed';
        $fotoRule = $this->userId ? 'nullable|mimes:jpg,jpeg,png,webp|max:2048' : 'required|mimes:jpg,jpeg,png,webp|max:2048';

        return [
            'username' => ['required', 'string', 'max:60', Rule::unique('users')->ignore($this->userId)],
            'email' => ['required', 'string', 'email', 'max:60', Rule::unique('users')->ignore($this->userId)],
            'password' => $passwordRule,
            'password_confirmation' => $this->userId ? 'nullable' : 'required_with:password',
            'foto' => $fotoRule,
            'role_id' => 'required|exists:roles,id', // Ganti dari roles_id
            'full_name' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'stay_purpose' => 'nullable|string|max:120',
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
        $searchTerm = '%'.$this->search.'%';
        $users = User::with('role')
            ->where(function ($query) use ($searchTerm) {
                $query->where('username', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm);
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.user-management', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->isModalOpen = true;
        $this->dispatch('modal:show', id: 'userModal');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $id;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        // Gunakan accessor foto_url agar konsisten
        $this->existingFoto = $user->foto_url;
        $this->role_id = $user->role_id;
        $this->full_name = $user->full_name;
        $this->phone = $user->phone;
        $this->address = $user->address;
        $this->city = $user->city;
        $this->province = $user->province;
        $this->date_of_birth = $user->date_of_birth ? (string) $user->date_of_birth : '';
        $this->gender = $user->gender;
        $this->stay_purpose = $user->stay_purpose;
        $this->isModalOpen = true;
        $this->dispatch('modal:show', id: 'userModal');
    }

    public function store()
    {
        $validatedData = $this->validate();

        $data = [
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'role_id' => $validatedData['role_id'],
            'full_name' => $validatedData['full_name'] ?? null,
            'phone' => $validatedData['phone'] ?? null,
            'address' => $validatedData['address'] ?? null,
            'city' => $validatedData['city'] ?? null,
            'province' => $validatedData['province'] ?? null,
            'date_of_birth' => $validatedData['date_of_birth'] ?? null,
            'gender' => $validatedData['gender'] ?? null,
            'stay_purpose' => $validatedData['stay_purpose'] ?? null,
        ];

        if (! empty($validatedData['password'])) {
            $data['password'] = Hash::make($validatedData['password']);
        }

        if ($this->foto) {
            if ($this->userId) {
                $oldPath = optional(User::find($this->userId))->foto;
                Uploader::deletePublicIfLocal($oldPath);
            }
            $data['foto'] = Uploader::storePublicImage($this->foto, 'fotos');
        }

        User::updateOrCreate(['id' => $this->userId], $data);

        $this->dispatch('swal:success', [
            'message' => $this->userId ? 'Data user berhasil diperbarui.' : 'User baru berhasil ditambahkan.',
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
        Uploader::deletePublicIfLocal($user->foto);
        $user->delete();

        $this->dispatch('swal:success', ['message' => 'Data user berhasil dihapus.']);
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->dispatch('modal:hide', id: 'userModal');
        $this->resetForm();
        $this->foto = null;
    }

    private function resetForm()
    {
        $this->reset(['userId', 'username', 'email', 'password', 'password_confirmation', 'foto', 'existingFoto', 'role_id', 'full_name', 'phone', 'address', 'city', 'province', 'date_of_birth', 'gender', 'stay_purpose']);
        $this->resetErrorBag();
    }

    protected $validationAttributes = [
        'username' => 'Username',
        'email' => 'Email',
        'password' => 'Password',
        'password_confirmation' => 'Konfirmasi password',
        'foto' => 'Foto profil',
        'role_id' => 'Peran',
        'full_name' => 'Nama lengkap',
        'phone' => 'No. telepon',
        'address' => 'Alamat',
        'city' => 'Kota',
        'province' => 'Provinsi',
        'date_of_birth' => 'Tanggal lahir',
        'gender' => 'Jenis kelamin',
        'stay_purpose' => 'Tujuan menginap',
    ];
}
