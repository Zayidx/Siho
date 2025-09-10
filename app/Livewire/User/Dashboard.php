<?php

namespace App\Livewire\User;

use App\Models\Bill;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.user')]
class Dashboard extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Title('Dashboard Pengguna')]
    public $unpaidBillsCount = 0;

    public function mount(): void
    {
        $this->unpaidBillsCount = Bill::whereHas('reservation', fn ($q) => $q->where('guest_id', Auth::id()))
            ->whereNull('paid_at')
            ->count();
    }

    public function getRecentReservationsProperty()
    {
        return Reservation::with('rooms')
            ->where('guest_id', Auth::id())
            ->latest()
            ->take(5)
            ->get();
    }

    public function getRecentBillsProperty()
    {
        return Bill::with('reservation')
            ->whereHas('reservation', fn ($q) => $q->where('guest_id', Auth::id()))
            ->latest()
            ->take(5)
            ->get();
    }

    public function getUpcomingReservationProperty()
    {
        return Reservation::with('rooms')
            ->where('guest_id', Auth::id())
            ->whereDate('check_in_date', '>=', now()->toDateString())
            ->orderBy('check_in_date')
            ->first();
    }

    public function render()
    {
        return view('livewire.user.dashboard', [
            'recentReservations' => $this->recent_reservations,
            'recentBills' => $this->recent_bills,
            'upcoming' => $this->upcoming_reservation,
        ]);
    }
}
