<?php

namespace App\Livewire;

use App\Models\Rooms; // Pastikan nama model benar
use App\Models\Reservations;
use App\Models\Bills;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Carbon\Carbon;

class BookingConfirmation extends Component
{
    public Rooms $room;
    public $checkinDate;
    public $checkoutDate;
    public $guests;
    public $nights;
    public $totalPrice;
    public $special_requests = '';

    public function mount(Rooms $room)
    {
        $this->room = $room;
        
        // Ambil data dari query string URL
        $this->checkinDate = request()->query('checkin');
        $this->checkoutDate = request()->query('checkout');
        $this->guests = request()->query('guests', 1);

        // Validasi dasar & perhitungan
        if ($this->checkinDate && $this->checkoutDate) {
            $start = Carbon::parse($this->checkinDate);
            $end = Carbon::parse($this->checkoutDate);
            $this->nights = $end->diffInDays($start);
            $this->totalPrice = $this->nights * $this->room->price_per_night;
        } else {
            // Handle jika data tidak lengkap, mungkin redirect kembali
            return redirect()->route('booking')->with('error', 'Booking details are incomplete.');
        }
    }

    public function render()
    {
        return view('livewire.booking-confirmation');
    }

    public function confirmBooking()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 1. Re-check availability within a transaction
        DB::transaction(function () {
            $isStillAvailable = !Rooms::where('id', $this->room->id)
                ->whereHas('reservations', function ($query) {
                    $query->where('check_out_date', '>', $this->checkinDate)
                          ->where('check_in_date', '<', $this->checkoutDate);
                })->exists();

            if (!$isStillAvailable) {
                session()->flash('error', 'Sorry, this room has just been booked by someone else. Please try another room or date.');
                return redirect()->route('booking');
            }

            // 2. Create Reservation
            $reservation = Reservations::create([
                'guest_id' => Auth::id(),
                'check_in_date' => $this->checkinDate,
                'check_out_date' => $this->checkoutDate,
                'status' => 'Confirmed',
                'special_requests' => $this->special_requests,
            ]);

            // 3. Attach Room to Reservation
            $reservation->rooms()->attach($this->room->id, ['assigned_at' => now()]);

            // 4. Create Bill
            Bills::create([
                'reservation_id' => $reservation->id,
                'amount' => $this->totalPrice,
                'status' => 'Unpaid',
                'due_date' => $this->checkinDate,
            ]);
            
            // Optional: Update room status if needed
            // $this->room->update(['status' => 'Booked']);
        });

        // 5. Redirect to dashboard with success message
        session()->flash('success', 'Your booking has been confirmed! Thank you for choosing Grand Luxe.');
        return redirect()->route('user.dashboard');
    }
}
