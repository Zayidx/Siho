<?php

namespace App\Livewire\Admin;
use Livewire\Attributes\Layout;

use Livewire\Component;
use App\Models\Rooms as Room;
use App\Models\RoomType;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;

#[Layout('components.layouts.app')]
class RoomManagement extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    #[Title("Manajemen Kamar")]
    public $isModalOpen = false;
    public $roomId, $room_number, $room_type_id, $status, $floor, $description, $price_per_night;
    public $roomTypes;

    public $search = '';
    public $perPage = 10;

    public function mount()
    {
        $this->roomTypes = RoomType::all();
    }

    protected function rules()
    {
        return [
            'room_number' => 'required|string|max:255|unique:rooms,room_number,' . $this->roomId,
            'room_type_id' => 'required|integer|exists:room_types,id',
            'status' => 'required|string|in:Available,Occupied,Cleaning,Dirty,Maintenance',
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
            'room_type_id.required' => 'Tipe kamar wajib dipilih.',
            'room_type_id.exists' => 'Tipe kamar tidak valid.',
            'status.required' => 'Status kamar wajib dipilih.',
            'status.in' => 'Status kamar tidak valid.',
            'floor.required' => 'Lantai wajib diisi.',
            'floor.integer' => 'Lantai harus berupa angka.',
            'floor.min' => 'Lantai minimal 1.',
            'description.string' => 'Deskripsi tidak valid.',
            'price_per_night.required' => 'Harga per malam wajib diisi.',
            'price_per_night.numeric' => 'Harga per malam harus berupa angka.',
            'price_per_night.min' => 'Harga per malam minimal 0.',
        ];
    }

    protected $validationAttributes = [
        'room_number' => 'Nomor kamar',
        'room_type_id' => 'Tipe kamar',
        'status' => 'Status',
        'floor' => 'Lantai',
        'description' => 'Deskripsi',
        'price_per_night' => 'Harga per malam',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $searchTerm = '%' . $this->search . '%';
        $rooms = Room::with('roomType')
            ->where(function ($q) use ($searchTerm) {
                $q->where('room_number', 'like', $searchTerm)
                  ->orWhereHas('roomType', function ($query) use ($searchTerm) {
                      $query->where('name', 'like', $searchTerm);
                  });
            })
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
        $this->dispatch('modal:show', id: 'roomModal');
    }

    public function edit($id)
    {
        $room = Room::findOrFail($id);
        $this->roomId = $id;
        $this->room_number = $room->room_number;
        $this->room_type_id = $room->room_type_id;
        $this->status = $room->status;
        $this->floor = $room->floor;
        $this->description = $room->description;
        $this->price_per_night = $room->price_per_night;
        $this->isModalOpen = true;
        $this->dispatch('modal:show', id: 'roomModal');
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

        if ($room->status == 'Occupied') {
            $this->dispatch('swal:error', [
                'message' => 'Aksi Gagal! Tidak dapat menghapus kamar yang sedang terisi.'
            ]);
            return;
        }

        $room->delete();

        $this->dispatch('swal:success', [
            'message' => 'Data kamar berhasil dihapus.'
        ]);
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->dispatch('modal:hide', id: 'roomModal');
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['roomId', 'room_number', 'room_type_id', 'status', 'floor', 'description', 'price_per_night']);
        $this->resetErrorBag();
    }
}
