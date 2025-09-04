<?php

namespace App\Livewire\Admin;

use App\Models\Bills;
use App\Models\User;
use App\Models\Reservations;
use App\Models\Rooms;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;


#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    #[Title('Admin Dashboard')]
    public $availableRooms;
    public $todaysCheckIns;
    public $monthlyRevenue; // [PERBAIKAN] Menambahkan tanda '$' yang hilang
    public $occupiedRooms;
    public $recentGuests;

    // Properti untuk data chart
    public $roomStatusData;
    public $weeklyReservationData;

    public function mount()
    {
        // Data untuk kartu statistik
        $this->availableRooms = Rooms::where('status', 'Available')->count();
        $this->todaysCheckIns = Reservations::whereDate('check_in_date', Carbon::today())->count();
        $this->monthlyRevenue = Bills::whereMonth('paid_at', Carbon::now()->month)
                                ->whereYear('paid_at', Carbon::now()->year)
                                ->sum('total_amount');
        $this->occupiedRooms = Rooms::where('status', 'Occupied')->count();

        // Data untuk tabel tamu terbaru (menggunakan users)
        $this->recentGuests = User::latest()->take(2)->get();

        // Data untuk chart status kamar (pie chart)
        $this->roomStatusData = [
            'available' => $this->availableRooms,
            'occupied' => $this->occupiedRooms,
            'cleaning' => Rooms::where('status', 'Cleaning')->count(),
        ];

        // Data untuk chart reservasi 7 hari terakhir (bar chart)
        $this->weeklyReservationData = Reservations::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
