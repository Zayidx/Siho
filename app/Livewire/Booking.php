<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Rooms; // Ganti nama model jika berbeda
use Carbon\Carbon;

class Booking extends Component
{
    public $checkinDate = '';
    public $checkoutDate = '';
    public $guests = 1;

    public $availableRooms = null;

    public function mount()
    {
        // Inisialisasi tanggal check-in ke hari ini
        $this->checkinDate = Carbon::today()->toDateString();
    }

    public function searchRooms()
    {
        $this->validate([
            'checkinDate' => 'required|date|after_or_equal:today',
            'checkoutDate' => 'required|date|after:checkinDate',
        ]);

        $checkin = $this->checkinDate;
        $checkout = $this->checkoutDate;

        // Cari kamar yang TIDAK memiliki reservasi yang tumpang tindih
        $this->availableRooms = Rooms::whereDoesntHave('reservations', function ($query) use ($checkin, $checkout) {
            $query->where(function ($q) use ($checkin, $checkout) {
                // Kondisi tumpang tindih: 
                // Reservasi berakhir setelah tanggal check-in baru
                // DAN reservasi dimulai sebelum tanggal check-out baru
                $q->where('check_out_date', '>', $checkin)
                  ->where('check_in_date', '<', $checkout);
            });
        })
        ->where('status', 'Available') // Pastikan status kamar juga 'Available'
        ->get();
    }

    public function bookRoom($roomId)
    {
        $this->validate([
            'checkinDate' => 'required|date',
            'checkoutDate' => 'required|date|after:checkinDate',
        ]);

        return redirect()->route('booking.confirm', [
            'room' => $roomId,
            'checkin' => $this->checkinDate,
            'checkout' => $this->checkoutDate,
            'guests' => $this->guests,
        ]);
    }
    
    public function render()
    {
        return view('livewire.booking');
    }
}
