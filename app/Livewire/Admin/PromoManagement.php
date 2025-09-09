<?php

namespace App\Livewire\Admin;
use Livewire\Attributes\Layout;

use App\Models\Promo;
use App\Models\RoomType;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PromoManagement extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    #[Title('Manajemen Promo')]
    public $search = '';
    public $perPage = 10;

    public $isModalOpen = false;
    public $promoId;
    public $code, $name, $discount_rate, $apply_room_type_id, $active = true, $valid_from, $valid_to, $usage_limit;

    public function updatingSearch(){ $this->resetPage(); }

    public function render()
    {
        $q = Promo::query()
            ->when($this->search, function($qq){
                $term = '%'.$this->search.'%';
                $qq->where('code','like',$term)->orWhere('name','like',$term);
            })
            ->latest();
        return view('livewire.admin.promo-management', [
            'promos' => $q->paginate($this->perPage),
            'roomTypes' => RoomType::orderBy('name')->get(),
        ]);
    }

    public function openModal($id = null)
    {
        $this->resetForm();
        if ($id) {
            $p = Promo::findOrFail($id);
            $this->promoId = $p->id;
            $this->code = $p->code;
            $this->name = $p->name;
            $this->discount_rate = $p->discount_rate;
            $this->active = $p->active;
            $this->apply_room_type_id = $p->apply_room_type_id;
            $this->valid_from = optional($p->valid_from)->format('Y-m-d\TH:i');
            $this->valid_to = optional($p->valid_to)->format('Y-m-d\TH:i');
            $this->usage_limit = $p->usage_limit;
        }
        $this->isModalOpen = true;
        $this->dispatch('modal:show', id: 'promoModal');
    }

    public function save()
    {
        $data = $this->validate([
            'code' => 'required|string|max:30|unique:promos,code,'.($this->promoId ?? 'NULL').',id',
            'name' => 'required|string|max:160',
            'discount_rate' => 'required|numeric|min:0|max:1',
            'apply_room_type_id' => 'nullable|exists:room_types,id',
            'active' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'usage_limit' => 'nullable|integer|min:1',
        ], [
            'code.required' => 'Kode promo wajib diisi.',
            'code.unique' => 'Kode promo sudah digunakan.',
            'name.required' => 'Nama promo wajib diisi.',
            'discount_rate.required' => 'Diskon wajib diisi.',
            'discount_rate.numeric' => 'Diskon harus berupa angka.',
            'discount_rate.min' => 'Diskon minimal 0.',
            'discount_rate.max' => 'Diskon maksimal 1 (100%).',
            'apply_room_type_id.exists' => 'Tipe kamar tidak valid.',
            'valid_to.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'usage_limit.integer' => 'Batas penggunaan harus berupa angka.',
            'usage_limit.min' => 'Batas penggunaan minimal 1.',
        ]);
        $data['code'] = strtoupper($data['code']);

        Promo::updateOrCreate(['id' => $this->promoId], $data);
        $this->dispatch('swal:success', ['message' => 'Promo disimpan.']);
        $this->closeModal();
    }

    public function delete($id)
    {
        Promo::findOrFail($id)->delete();
        $this->dispatch('swal:success', ['message' => 'Promo dihapus.']);
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->dispatch('modal:hide', id: 'promoModal');
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['promoId','code','name','discount_rate','apply_room_type_id','active','valid_from','valid_to','usage_limit']);
        $this->active = true;
        $this->resetErrorBag();
    }

    protected $validationAttributes = [
        'code' => 'Kode promo',
        'name' => 'Nama promo',
        'discount_rate' => 'Diskon',
        'apply_room_type_id' => 'Tipe kamar',
        'valid_from' => 'Tanggal mulai',
        'valid_to' => 'Tanggal selesai',
        'usage_limit' => 'Batas penggunaan',
    ];
}
