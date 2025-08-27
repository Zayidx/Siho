<?php

namespace App\Livewire\Public;

use App\Models\Rooms;
use App\Models\RoomType;
use App\Models\Facility;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.public')]
#[Title('Daftar Kamar')]
class RoomsList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $roomType = '';
    public $facilityIds = [];
    public $minPrice = '';
    public $maxPrice = '';
    public $perPage = 12;
    public $minCapacity = '';
    public $sort = 'price_asc';
    public $checkin = '';
    public $checkout = '';
    public $guests = 1;
    public $chips = [];

    public function updatingSearch(){ $this->resetPage(); }
    public function updatingRoomType(){ $this->resetPage(); }
    public function updatingFacilityIds(){ $this->resetPage(); }
    public function updatingMinPrice(){ $this->resetPage(); }
    public function updatingMaxPrice(){ $this->resetPage(); }
    public function clearFilters(){
        $this->reset(['search','roomType','facilityIds','minPrice','maxPrice','minCapacity','sort']);
        $this->sort = 'price_asc';
        $this->resetPage();
    }

    public function render()
    {
        $q = Rooms::with(['roomType','images'])
            ->when($this->search, fn($qq) => $qq->where(function($w){
                $w->where('room_number','like','%'.$this->search.'%')
                  ->orWhere('description','like','%'.$this->search.'%');
            }))
            ->when($this->minPrice, fn($qq) => $qq->where('price_per_night','>=',(float)$this->minPrice))
            ->when($this->maxPrice, fn($qq) => $qq->where('price_per_night','<=',(float)$this->maxPrice))
            ->when($this->roomType, fn($qq) => $qq->where('room_type_id', $this->roomType))
            ->when($this->minCapacity, function($qq){
                $qq->whereHas('roomType', function($w){
                    $w->where('capacity', '>=', (int)$this->minCapacity);
                });
            })
            ->when(!empty($this->facilityIds), function($qq){
                // filter via room type facilities
                $facilityIds = (array) $this->facilityIds;
                $qq->whereHas('roomType.facilities', function($f) use ($facilityIds){
                    $f->whereIn('facilities.id', $facilityIds);
                });
            });

        if ($this->sort === 'popular') {
            $q->withCount('reservations')->orderByDesc('reservations_count');
        } elseif ($this->sort === 'price_desc') {
            $q->orderByDesc('price_per_night');
        } elseif ($this->sort === 'newest') {
            $q->latest('created_at');
        } else {
            $q->orderBy('price_per_night');
        }

        $recommended = Rooms::with(['roomType','images'])
            ->where('status','Available')
            ->withCount('reservations')
            ->orderByDesc('reservations_count')
            ->take(6)->get();

        return view('livewire.public.rooms-list', [
            'rooms' => $q->paginate($this->perPage),
            'types' => RoomType::orderBy('name')->get(),
            'facilities' => Facility::orderBy('name')->get(),
            'recommended' => $recommended,
            'disabledDates' => $this->fullyBookedDates,
        ]);
    }

    public function getFullyBookedDatesProperty()
    {
        // Next 45 days: mark dates where no rooms are available
        $dates = [];
        $start = now();
        $end = now()->addDays(45);
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $in = $d->toDateString();
            $out = $d->copy()->addDay()->toDateString();
            $availableCount = Rooms::whereDoesntHave('reservations', function ($query) use ($in, $out) {
                $query->where(function ($q) use ($in, $out) {
                    $q->where('check_out_date', '>', $in)
                        ->where('check_in_date', '<', $out);
                });
            })->where('status','Available')->count();
            if ($availableCount === 0) {
                $dates[] = $in;
            }
        }
        return $dates;
    }
}
