<?php

namespace App\Livewire\Admin;

use App\Models\Rooms;
use App\Models\RoomImage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class RoomImages extends Component
{
    use WithFileUploads;

    public Rooms $room;
    public $photos = [];

    #[Title('Kelola Foto Kamar')]
    public function mount(Rooms $room)
    {
        $this->room = $room->load('images');
    }

    public function render()
    {
        return view('livewire.admin.room-images');
    }

    public function upload()
    {
        $this->validate([
            'photos.*' => 'image|max:4096',
        ]);

        foreach ((array) $this->photos as $file) {
            $path = $file->store('room_images', 'public');
            RoomImage::create([
                'room_id' => $this->room->id,
                'path' => $path,
                'sort_order' => ($this->room->images()->max('sort_order') ?? 0) + 1,
            ]);
        }
        $this->reset('photos');
        $this->room->refresh();
        $this->dispatch('swal:success', ['message' => 'Foto berhasil diunggah.']);
    }

    public function delete($id)
    {
        $img = RoomImage::where('room_id', $this->room->id)->findOrFail($id);
        Storage::disk('public')->delete($img->path);
        $img->delete();
        $this->room->refresh();
        $this->dispatch('swal:success', ['message' => 'Foto dihapus.']);
    }
}

