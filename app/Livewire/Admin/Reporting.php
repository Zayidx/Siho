<?php

namespace App\Livewire\Admin;
use Livewire\Attributes\Layout;

use App\Models\Bills;
use App\Models\Reservations;
use App\Models\Rooms;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Reporting extends Component
{
    public $startDate;
    public $endDate;

    public $revenueData;
    public $occupancyData;

    public function mount()
    {
        $this->endDate = Carbon::today()->toDateString();
        $this->startDate = Carbon::today()->subDays(29)->toDateString();
        $this->generateReport();
    }

    public function generateReport()
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ], [
            'startDate.required' => 'Tanggal mulai wajib diisi.',
            'startDate.date' => 'Tanggal mulai tidak valid.',
            'endDate.required' => 'Tanggal akhir wajib diisi.',
            'endDate.date' => 'Tanggal akhir tidak valid.',
            'endDate.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal mulai.',
        ]);

        $this->generateRevenueData();
        $this->generateOccupancyData();

        // Dispatch event to re-initialize charts in the browser
        $this->dispatch('reportGenerated');
    }

    private function generateRevenueData()
    {
        $data = Bills::whereBetween('created_at', [$this->startDate, Carbon::parse($this->endDate)->endOfDay()])
            ->groupBy('date')
            ->orderBy('date')
            ->get([
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total')
            ])
            ->pluck('total', 'date');

        $this->revenueData = $this->prepareChartData($data);
    }

    private function generateOccupancyData()
    {
        $totalRooms = Rooms::count();
        if ($totalRooms == 0) {
            $this->occupancyData = $this->prepareChartData(collect([]));
            return;
        }

        $period = CarbonPeriod::create($this->startDate, $this->endDate);
        $occupancy = [];

        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            // Hitung jumlah kamar yang terisi pada tanggal tersebut (overlap interval)
            $occupiedRooms = \DB::table('reservation_room')
                ->join('reservations', 'reservation_room.reservation_id', '=', 'reservations.id')
                ->where('reservations.check_in_date', '<=', $dateStr)
                ->where('reservations.check_out_date', '>', $dateStr)
                ->distinct('reservation_room.room_id')
                ->count('reservation_room.room_id');

            $occupancy[$dateStr] = round(($occupiedRooms / $totalRooms) * 100, 2);
        }

        $this->occupancyData = $this->prepareChartData(collect($occupancy));
    }

    private function prepareChartData($data)
    {
        $period = CarbonPeriod::create($this->startDate, $this->endDate);
        $labels = [];
        $values = [];

        foreach ($period as $date) {
            $dateString = $date->toDateString();
            $labels[] = $date->format('M d');
            $values[] = $data[$dateString] ?? 0;
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    public function render()
    {
        return view('livewire.admin.reporting');
    }

    protected $validationAttributes = [
        'startDate' => 'Tanggal mulai',
        'endDate' => 'Tanggal akhir',
    ];
}
