<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;


class GuestManagement extends Component
{
    use WithFileUploads, WithPagination;
    protected $paginationTheme = 'bootstrap';

    #[Title("Manajemen Tamu")]
    public $isModalOpen = false;
    public $guestId, $full_name, $email, $phone, $address, $id_number, $date_of_birth;
    public $photo, $existingPhoto;

    public $search = '';
    public $perPage = 10;

    protected function rules()
    {
        // Foto wajib saat membuat, opsional saat edit
        $photoRule = $this->guestId ? 'nullable|image|max:2048' : 'required|image|max:2048';

        return [
            'full_name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->guestId)],
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'id_number' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($this->guestId)],
            'date_of_birth' => 'nullable|date',
            'photo' => $photoRule,
        ];
    }

    protected function messages()
    {
        return [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'photo.required' => 'Foto wajib diunggah.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal 2MB.',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $searchTerm = '%' . $this->search . '%';
        $guests = User::where('full_name', 'like', $searchTerm)
                       ->orWhere('email', 'like', $searchTerm)
                       ->latest()
                       ->paginate($this->perPage);

        return view('livewire.admin.guest-management', [
            'guests' => $guests
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $guest = User::findOrFail($id);
        $this->guestId = $id;
        $this->full_name = $guest->full_name;
        $this->email = $guest->email;
        $this->phone = $guest->phone;
        $this->address = $guest->address;
        $this->id_number = $guest->id_number;
        $this->date_of_birth = $guest->date_of_birth;
        $this->existingPhoto = $guest->photo ? Storage::url($guest->photo) : null;
        $this->isModalOpen = true;
    }

    public function store()
    {
        $validatedData = $this->validate();

        if ($this->photo) {
            if ($this->guestId && $this->existingPhoto) {
                $oldPath = str_replace('/storage/', '', $this->existingPhoto);
                Storage::disk('public')->delete($oldPath);
            }
            $validatedData['photo'] = $this->photo->store('guest-photos', 'public');
        }

        User::updateOrCreate(['id' => $this->guestId], $validatedData);

        $this->dispatch('swal:success', [
            'message' => $this->guestId ? 'Data tamu berhasil diperbarui.' : 'Tamu baru berhasil ditambahkan.'
        ]);

        $this->closeModal();
    }

    #[On('destroy')]
    public function destroy($id)
    {
        $guest = User::with('reservations')->findOrFail($id);

        // Cek jika tamu punya reservasi yang belum selesai
        $activeReservations = $guest->reservations()->where('status', '!=', 'Completed')->count();
        if ($activeReservations > 0) {
            $this->dispatch('swal:error', [
                'message' => 'Aksi Gagal! Tamu ini memiliki reservasi yang masih aktif.'
            ]);
            return;
        }

        if ($guest->photo) {
            Storage::disk('public')->delete($guest->photo);
        }
        $guest->delete();

        $this->dispatch('swal:success', [
            'message' => 'Data tamu berhasil dihapus.'
        ]);
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['guestId', 'full_name', 'email', 'phone', 'address', 'id_number', 'date_of_birth', 'photo', 'existingPhoto']);
        $this->resetErrorBag();
    }
}
