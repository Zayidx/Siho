<?php

namespace App\Livewire\Admin;

use App\Models\Facility;
use App\Models\RoomType;
use Livewire\Component;
use Livewire\WithPagination;

class RoomTypeManagement extends Component
{
    use WithPagination;

    public $isOpen = false;
    public $roomTypeId, $name, $description, $base_price, $capacity;
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
            'name' => 'required',
            'base_price' => 'required|numeric',
            'capacity' => 'required|integer',
            'selectedFacilities' => 'array'
        ]);

        $roomType = RoomType::updateOrCreate(['id' => $this->roomTypeId], [
            'name' => $this->name,
            'description' => $this->description,
            'base_price' => $this->base_price,
            'capacity' => $this->capacity,
        ]);

        $roomType->facilities()->sync($this->selectedFacilities);

        $this->dispatch('swal:success', [
            'message' => $this->roomTypeId ? 'Room Type Updated Successfully.' : 'Room Type Created Successfully.'
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
            'message' => 'Room Type Deleted Successfully.'
        ]);
    }
}
