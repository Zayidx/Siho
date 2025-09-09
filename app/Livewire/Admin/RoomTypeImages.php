<?php

namespace App\Livewire\Admin;

use App\Models\RoomImage;
use App\Models\RoomType;
use App\Support\Uploads\Uploader;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class RoomTypeImages extends Component
{
    use WithFileUploads;

    public RoomType $type;

    public $photos = [];

    public array $categories = [
        '' => 'Tanpa Kategori',
        'facade' => 'Fasad',
        'facilities' => 'Fasilitas',
        'public' => 'Public Space',
        'restaurant' => 'Restoran',
        'room' => 'Kamar',
    ];

    #[Title('Kelola Foto Tipe Kamar')]
    public function mount(RoomType $type)
    {
        $this->type = $type->load('images');
    }

    public function render()
    {
        return view('livewire.admin.room-type-images');
    }

    // Rename to avoid collision with Livewire's $wire.upload client API
    public function savePhotos()
    {
        $this->validate([
            'photos' => 'required|array|min:1',
            'photos.*' => 'mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'photos.required' => 'Pilih minimal satu foto.',
            'photos.array' => 'Format unggahan tidak valid.',
            'photos.*.mimes' => 'Format harus jpg, jpeg, png, atau webp.',
            'photos.*.max' => 'Ukuran foto maksimal 4MB.',
        ]);

        foreach ((array) $this->photos as $file) {
            $path = Uploader::storePublicImage($file, 'room_images');
            $sort = ($this->type->images()->max('sort_order') ?? 0) + 1;
            RoomImage::create([
                'room_type_id' => $this->type->id,
                'path' => $path,
                'sort_order' => $sort,
                'is_cover' => false,
            ]);
        }
        $this->reset('photos');
        $this->type->refresh()->load('images');
        $this->dispatch('swal:success', ['message' => 'Foto berhasil diunggah.']);
    }

    public function delete($id)
    {
        $img = RoomImage::where('room_type_id', $this->type->id)->findOrFail($id);
        Uploader::deletePublicIfLocal($img->path);
        $img->delete();
        $this->type->refresh()->load('images');
        $this->dispatch('swal:success', ['message' => 'Foto dihapus.']);
    }

    public function setCover($id)
    {
        $img = RoomImage::where('room_type_id', $this->type->id)->findOrFail($id);
        RoomImage::where('room_type_id', $this->type->id)->where('id', '!=', $img->id)->update(['is_cover' => false]);
        $img->update(['is_cover' => true]);
        $this->type->refresh()->load('images');
        $this->dispatch('swal:success', ['message' => 'Cover foto diperbarui.']);
    }

    public function moveUp($id)
    {
        $img = RoomImage::where('room_type_id', $this->type->id)->findOrFail($id);
        $prev = RoomImage::where('room_type_id', $this->type->id)
            ->where('sort_order', '<', $img->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();
        if ($prev) {
            [$img->sort_order, $prev->sort_order] = [$prev->sort_order, $img->sort_order];
            $img->save();
            $prev->save();
            $this->type->refresh()->load('images');
        }
    }

    public function moveDown($id)
    {
        $img = RoomImage::where('room_type_id', $this->type->id)->findOrFail($id);
        $next = RoomImage::where('room_type_id', $this->type->id)
            ->where('sort_order', '>', $img->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();
        if ($next) {
            [$img->sort_order, $next->sort_order] = [$next->sort_order, $img->sort_order];
            $img->save();
            $next->save();
            $this->type->refresh()->load('images');
        }
    }

    public function setCategory($id, $category)
    {
        $img = RoomImage::where('room_type_id', $this->type->id)->findOrFail($id);
        $category = (string) $category;
        if (! array_key_exists($category, $this->categories)) {
            $category = null;
        }
        $img->update(['category' => $category ?: null]);
        $this->type->refresh()->load('images');
        $this->dispatch('swal:success', ['message' => 'Kategori foto diperbarui.']);
    }

    protected $validationAttributes = [
        'photos' => 'Foto',
        'photos.*' => 'Foto',
    ];
}
