<?php

namespace App\Livewire\Admin;

use App\Models\RoomImage;
use App\Models\RoomType;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

class RoomTypeImages extends Component
{
    use WithFileUploads;

    public RoomType $type;
    public $photos = [];

    #[Title('Kelola Foto Tipe Kamar')]
    public function mount(RoomType $type)
    {
        $this->type = $type->load('images');
    }

    public function render()
    {
        return view('livewire.admin.room-type-images');
    }

    public function upload()
    {
        $this->validate([
            'photos.*' => 'image|max:4096',
        ]);

        foreach ((array) $this->photos as $file) {
            $path = $file->store('room_images', 'public');
            $sort = ($this->type->images()->max('sort_order') ?? 0) + 1;
            RoomImage::create([
                'room_type_id' => $this->type->id,
                'path' => $path,
                'sort_order' => $sort,
            ]);
        }
        $this->reset('photos');
        $this->type->refresh()->load('images');
        $this->dispatch('swal:success', ['message' => 'Foto berhasil diunggah.']);
    }

    public function delete($id)
    {
        $img = RoomImage::where('room_type_id', $this->type->id)->findOrFail($id);
        Storage::disk('public')->delete($img->path);
        $img->delete();
        $this->type->refresh()->load('images');
        $this->dispatch('swal:success', ['message' => 'Foto dihapus.']);
    }
}

