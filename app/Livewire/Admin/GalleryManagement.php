<?php

namespace App\Livewire\Admin;
use Livewire\Attributes\Layout;

use App\Models\HotelGallery;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Support\Uploads\Uploader;

#[Title('Manajemen Galeri Hotel')]
#[Layout('components.layouts.app')]
class GalleryManagement extends Component
{
    use WithFileUploads;

    public $photos = [];
    public array $categories = [
        '' => 'Tanpa Kategori',
        'facade' => 'Fasad',
        'facilities' => 'Fasilitas',
        'public' => 'Public Space',
        'restaurant' => 'Restoran',
        'room' => 'Kamar',
    ];

    public function render()
    {
        $rows = HotelGallery::orderByDesc('is_cover')->orderBy('sort_order')->orderByDesc('created_at')->get();
        return view('livewire.admin.gallery-management', compact('rows'));
    }

    public function savePhotos()
    {
        $this->validate([
            'photos' => 'required|array|min:1',
            'photos.*' => 'mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'photos.required' => 'Pilih minimal satu foto.',
            'photos.array' => 'Format unggahan tidak valid.',
            'photos.*.mimes' => 'Format foto harus jpg, jpeg, png, atau webp.',
            'photos.*.max' => 'Ukuran foto maksimal 4MB.',
        ]);
        foreach ((array) $this->photos as $file) {
            $path = Uploader::storePublicImage($file, 'gallery');
            $sort = (HotelGallery::max('sort_order') ?? 0) + 1;
            HotelGallery::create([
                'path' => $path,
                'sort_order' => $sort,
                'is_cover' => false,
            ]);
        }
        $this->reset('photos');
        $this->dispatch('swal:success', ['message' => 'Foto galeri berhasil diunggah.']);
    }

    public function delete($id)
    {
        $img = HotelGallery::findOrFail($id);
        Uploader::deletePublicIfLocal($img->path);
        $img->delete();
        $this->dispatch('swal:success', ['message' => 'Foto galeri dihapus.']);
    }

    public function setCover($id)
    {
        $img = HotelGallery::findOrFail($id);
        HotelGallery::where('id', '!=', $img->id)->update(['is_cover' => false]);
        $img->update(['is_cover' => true]);
        $this->dispatch('swal:success', ['message' => 'Cover galeri diperbarui.']);
    }

    public function moveUp($id)
    {
        $img = HotelGallery::findOrFail($id);
        $prev = HotelGallery::where('sort_order', '<', $img->sort_order)->orderBy('sort_order', 'desc')->first();
        if ($prev) {
            [$img->sort_order, $prev->sort_order] = [$prev->sort_order, $img->sort_order];
            $img->save();
            $prev->save();
        }
    }

    public function moveDown($id)
    {
        $img = HotelGallery::findOrFail($id);
        $next = HotelGallery::where('sort_order', '>', $img->sort_order)->orderBy('sort_order', 'asc')->first();
        if ($next) {
            [$img->sort_order, $next->sort_order] = [$next->sort_order, $img->sort_order];
            $img->save();
            $next->save();
        }
    }

    public function setCategory($id, $category)
    {
        $img = HotelGallery::findOrFail($id);
        $category = (string) $category;
        if (!array_key_exists($category, $this->categories)) {
            $category = null;
        }
        $img->update(['category' => $category ?: null]);
        $this->dispatch('swal:success', ['message' => 'Kategori galeri diperbarui.']);
    }

    protected $validationAttributes = [
        'photos' => 'Foto',
        'photos.*' => 'Foto',
    ];
}
