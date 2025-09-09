<?php

namespace App\Livewire\Admin;

use App\Models\Facility;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class FacilityManagement extends Component
{
    use WithPagination;

    #[Title('Facility Management')]
    public $isOpen = false;

    public $facilityId;

    public $name;

    public $icon;

    public $deleteId = '';

    protected $listeners = ['deleteConfirmed' => 'destroy'];

    public function render()
    {
        $facilities = Facility::paginate(10);

        return view('livewire.admin.facility-management', [
            'facilities' => $facilities,
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
        $this->facilityId = null;
        $this->name = '';
        $this->icon = '';
        $this->deleteId = '';
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama fasilitas wajib diisi.',
            'name.string' => 'Nama fasilitas tidak valid.',
            'name.max' => 'Nama fasilitas terlalu panjang.',
        ]);

        Facility::updateOrCreate(['id' => $this->facilityId], [
            'name' => $this->name,
        ]);

        $this->dispatch('swal:success', [
            'message' => $this->facilityId ? 'Fasilitas berhasil diperbarui.' : 'Fasilitas berhasil dibuat.',
        ]);

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $facility = Facility::findOrFail($id);
        $this->facilityId = $id;
        $this->name = $facility->name;
        $this->icon = $facility->icon;

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
        Facility::find($this->deleteId)->delete();
        $this->dispatch('swal:success', [
            'message' => 'Fasilitas berhasil dihapus.',
        ]);
    }

    protected $validationAttributes = [
        'name' => 'Nama fasilitas',
    ];
}
