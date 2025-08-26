<?php

namespace App\Livewire\Admin;

use App\Models\Bills;
use App\Models\Reservations;
use App\Models\Rooms;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

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
            $occupiedRooms = Reservations::where('check_in_date', '<=', $date->toDateString())
                ->where('check_out_date', '>', $date->toDateString())
                ->count();
            
            $occupancy[$date->toDateString()] = round(($occupiedRooms / $totalRooms) * 100, 2);
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
}