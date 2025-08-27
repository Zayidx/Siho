<?php

namespace App\Livewire\User;

use App\Models\Reservations;
use App\Models\Rooms;
use App\Models\Bills;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.user')]
class Dashboard extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    #[Title('Dashboard Pengguna')]
    public $unpaidBillsCount = 0;

    public function mount(): void
    {
        $this->unpaidBillsCount = Bills::whereHas('reservation', fn ($q) => $q->where('guest_id', auth()->id()))
            ->whereNull('paid_at')
            ->count();
    }

    public function getRecentReservationsProperty()
    {
        return Reservations::with('rooms')
            ->where('guest_id', Auth::id())
            ->latest()
            ->take(5)
            ->get();
    }

    public function getRecentBillsProperty()
    {
        return Bills::with('reservation')
            ->whereHas('reservation', fn ($q) => $q->where('guest_id', Auth::id()))
            ->latest()
            ->take(5)
            ->get();
    }

    public function getUpcomingReservationProperty()
    {
        return Reservations::with('rooms')
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
