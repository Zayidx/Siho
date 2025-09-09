<?php

namespace App\Livewire\Admin;

use App\Models\Facility;
use App\Models\RoomType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class RoomTypeManagement extends Component
{
    use WithPagination;

    #[Title('Room Type Management')]
    public $isOpen = false;

    public $roomTypeId;

    public $name;

    public $description;

    public $base_price;

    public $capacity;

    public $deleteId = '';

    public $allFacilities;

    public $selectedFacilities = [];

    protected $listeners = ['deleteConfirmed' => 'destroy'];

    public function mount()
    {
        $this->allFacilities = Facility::all();
    }

    public function render()
    {
        $roomTypes = RoomType::with('facilities')->paginate(10);

        return view('livewire.admin.room-type-management', [
            'roomTypes' => $roomTypes,
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    private function resetInputFields()
    {
        $this->roomTypeId = null;
        $this->name = '';
        $this->description = '';
        $this->base_price = '';
        $this->capacity = '';
        $this->deleteId = '';
        $this->selectedFacilities = [];
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:160',
            'base_price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'selectedFacilities' => 'array',
        ], [
            'name.required' => 'Nama tipe kamar wajib diisi.',
            'name.string' => 'Nama tipe kamar tidak valid.',
            'name.max' => 'Nama tipe kamar terlalu panjang.',
            'base_price.required' => 'Harga dasar wajib diisi.',
            'base_price.numeric' => 'Harga dasar harus berupa angka.',
            'base_price.min' => 'Harga dasar minimal 0.',
            'capacity.required' => 'Kapasitas wajib diisi.',
            'capacity.integer' => 'Kapasitas harus berupa angka.',
            'capacity.min' => 'Kapasitas minimal 1.',
        ]);

        $roomType = RoomType::updateOrCreate(['id' => $this->roomTypeId], [
            'name' => $this->name,
            'description' => $this->description,
            'base_price' => $this->base_price,
            'capacity' => $this->capacity,
        ]);

        $roomType->facilities()->sync($this->selectedFacilities);

        $this->dispatch('swal:success', [
            'message' => $this->roomTypeId ? 'Tipe kamar berhasil diperbarui.' : 'Tipe kamar berhasil dibuat.',
        ]);

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $roomType = RoomType::with('facilities')->findOrFail($id);
        $this->roomTypeId = $id;
        $this->name = $roomType->name;
        $this->description = $roomType->description;
        $this->base_price = $roomType->base_price;
        $this->capacity = $roomType->capacity;
        $this->selectedFacilities = $roomType->facilities->pluck('id')->toArray();

        $this->openModal();
    }

    public function delete($id)
    {
        $this->deleteId = $id;
        $this->dispatch('swal:confirm', [
            'method' => 'deleteConfirmed',
            'id' => $id,
        ]);
    }

    public function destroy()
    {
        RoomType::find($this->deleteId)->delete();
        $this->dispatch('swal:success', [
            'message' => 'Tipe kamar berhasil dihapus.',
        ]);
    }
}
