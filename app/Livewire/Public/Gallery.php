<?php

namespace App\Livewire\Public;

use App\Models\HotelGallery;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.public')]
#[Title('Galeri Hotel')]
class Gallery extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $category = '';

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function render()
    {
        $cats = [
            '' => 'Semua',
            'facade' => 'Fasad',
            'facilities' => 'Fasilitas',
            'public' => 'Public Space',
            'restaurant' => 'Restoran',
            'room' => 'Kamar',
        ];
        $q = HotelGallery::query();
        if ($this->category !== '') {
            $q->where('category', $this->category);
        }
        $q->orderByDesc('is_cover')->orderBy('sort_order')->orderByDesc('created_at');

        return view('livewire.public.gallery', [
            'categories' => $cats,
            'rows' => $q->paginate(18),
        ]);
    }
}
