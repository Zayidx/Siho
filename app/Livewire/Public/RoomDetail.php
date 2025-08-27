<?php

namespace App\Livewire\Public;

use App\Models\Rooms;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.public')]
class RoomDetail extends Component
{
    public Rooms $room;
    public $bookedRanges = [];
    public $similarRooms = [];

    public function mount(Rooms $room)
    {
        $this->room = $room->load(['roomType.facilities','images','reservations'=>function($q){
            $q->where('check_out_date','>=', now()->toDateString())->orderBy('check_in_date');
        }]);
        $this->bookedRanges = $this->room->reservations->map(fn($r)=>[
            'from' => $r->check_in_date->format('Y-m-d'),
            'to' => $r->check_out_date->format('Y-m-d'),
        ])->take(8)->toArray();
        $this->similarRooms = Rooms::with('roomType','images')
            ->where('room_type_id', $this->room->room_type_id)
            ->where('id','!=',$this->room->id)
            ->orderBy('price_per_night')
            ->take(3)->get();
    }

    public function render()
    {
        return view('livewire.public.room-detail');
    }
}
