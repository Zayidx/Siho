<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Rooms as Room;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;

class RoomManagement extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    #[Title("Manajemen Kamar")]
    public $isModalOpen = false;
    public $roomId, $room_number, $room_type, $status, $floor, $description, $price_per_night;

    public $search = '';
    public $perPage = 10;

    protected function rules()
    {
        return [
            'room_number' => 'required|string|max:255|unique:rooms,room_number,' . $this->roomId,
            'room_type' => 'required|string|in:Standard,Deluxe,Suite',
            'status' => 'required|string|in:Available,Occupied,Cleaning',
            'floor' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'price_per_night' => 'required|numeric|min:0',
        ];
    }

    protected function messages()
    {
        return [
            'room_number.required' => 'Nomor kamar wajib diisi.',
            'room_number.unique' => 'Nomor kamar sudah ada.',
            'room_type.required' => 'Tipe kamar wajib dipilih.',
            'status.required' => 'Status kamar wajib dipilih.',
            'floor.required' => 'Lantai wajib diisi.',
            'price_per_night.required' => 'Harga per malam wajib diisi.',
            'price_per_night.numeric' => 'Harga harus berupa angka.',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $searchTerm = '%' . $this->search . '%';
        $rooms = Room::where('room_number', 'like', $searchTerm)
                     ->orWhere('room_type', 'like', $searchTerm)
                     ->latest()
                     ->paginate($this->perPage);

        return view('livewire.admin.room-management', [
            'rooms' => $rooms
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $room = Room::findOrFail($id);
        $this->roomId = $id;
        $this->room_number = $room->room_number;
        $this->room_type = $room->room_type;
        $this->status = $room->status;
        $this->floor = $room->floor;
        $this->description = $room->description;
        $this->price_per_night = $room->price_per_night;
        $this->isModalOpen = true;
    }

    public function store()
    {
        $validatedData = $this->validate();

        Room::updateOrCreate(['id' => $this->roomId], $validatedData);

        $this->dispatch('swal:success', [
            'message' => $this->roomId ? 'Data kamar berhasil diperbarui.' : 'Kamar baru berhasil ditambahkan.'
        ]);

        $this->closeModal();
    }

    #[On('destroy')]
    public function destroy($id)
    {
        $room = Room::findOrFail($id);

        // [UPDATE] Tambahkan logika pengecekan status kamar
        if ($room->status == 'Occupied') {
            $this->dispatch('swal:error', [
                'message' => 'Aksi Gagal! Tidak dapat menghapus kamar yang sedang terisi.'
            ]);
            return; // Hentikan eksekusi
        }

        $room->delete();

        $this->dispatch('swal:success', [
            'message' => 'Data kamar berhasil dihapus.'
        ]);
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['roomId', 'room_number', 'room_type', 'status', 'floor', 'description', 'price_per_night']);
        $this->resetErrorBag();
    }
}
