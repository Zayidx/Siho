<?php

namespace App\Livewire\User;

use App\Models\Reservations;
use App\Models\Rooms;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    #[Title('Dashboard Pengguna')]
    public $check_in_date, $check_out_date, $special_requests;

    public $availableRoomTypes = [];
    public $selectedRoomTypes = [];

    public function mount()
    {
        $this->loadAvailableRoomTypes();
    }

    public function loadAvailableRoomTypes(): void
    {
        $this->availableRoomTypes = Rooms::query()
            ->where('status', 'Available')
            ->select(
                'room_type',
                DB::raw('count(*) as available_count'),
                DB::raw('avg(price_per_night) as avg_price')
            )
            ->groupBy('room_type')
            ->get()
            ->keyBy('room_type')
            ->map(function ($row) {
                return [
                    'available_count' => (int) ($row->available_count ?? 0),
                    'avg_price' => (int) round($row->avg_price ?? 0),
                ];
            })
            ->toArray();

        // Inisialisasi counter saat create
        $this->selectedRoomTypes = collect($this->availableRoomTypes)
            ->mapWithKeys(fn ($type, $key) => [$key => $this->selectedRoomTypes[$key] ?? 0])
            ->toArray();
    }

    public function incrementRoomType($type)
    {
        $availableCount = $this->availableRoomTypes[$type]['available_count'] ?? 0;
        $current = $this->selectedRoomTypes[$type] ?? 0;
        if ($current < $availableCount) {
            $this->selectedRoomTypes[$type] = $current + 1;
        }
    }

    public function decrementRoomType($type)
    {
        $current = $this->selectedRoomTypes[$type] ?? 0;
        if ($current > 0) {
            $this->selectedRoomTypes[$type] = $current - 1;
        }
    }

    public function createReservation()
    {
        $this->validate([
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'selectedRoomTypes' => ['required', 'array', function ($attribute, $value, $fail) {
                if (collect($value)->sum() <= 0) {
                    $fail('Minimal satu kamar harus dipilih.');
                }
            }],
            'special_requests' => 'nullable|string|max:1000',
        ]);

        $roomIdsToAttach = [];
        foreach ($this->selectedRoomTypes as $type => $count) {
            $count = (int) $count;
            if ($count > 0) {
                $ids = Rooms::where('room_type', $type)
                    ->where('status', 'Available')
                    ->limit($count)
                    ->pluck('id')
                    ->toArray();
                if (count($ids) < $count) {
                    $this->addError('selectedRoomTypes', "Ketersediaan kamar tipe $type tidak mencukupi.");
                    $this->loadAvailableRoomTypes();
                    return;
                }
                $roomIdsToAttach = array_merge($roomIdsToAttach, $ids);
            }
        }

        $reservation = null;
        DB::transaction(function () use (&$reservation, $roomIdsToAttach) {
            $reservation = Reservations::create([
                'guest_id' => Auth::id(),
                'check_in_date' => $this->check_in_date,
                'check_out_date' => $this->check_out_date,
                'status' => 'Confirmed',
                'special_requests' => $this->special_requests,
            ]);

            $reservation->rooms()->attach($roomIdsToAttach);
            Rooms::whereIn('id', $roomIdsToAttach)->update(['status' => 'Occupied']);
        });

        $this->dispatch('swal:success', ['message' => 'Reservasi berhasil dibuat.']);
        $this->reset(['check_in_date', 'check_out_date', 'special_requests']);
        // reset pilihan kamar setelah create
        $this->selectedRoomTypes = collect($this->availableRoomTypes)
            ->mapWithKeys(fn ($type, $key) => [$key => 0])
            ->toArray();
        $this->loadAvailableRoomTypes();
    }

    public function cancelReservation($id)
    {
        $reservation = Reservations::with('rooms')->where('guest_id', Auth::id())->findOrFail($id);
        if (in_array($reservation->status, ['Checked-in', 'Completed'])) {
            $this->dispatch('swal:error', ['message' => 'Reservasi tidak dapat dibatalkan.']);
            return;
        }

        DB::transaction(function () use ($reservation) {
            $roomIds = $reservation->rooms()->pluck('rooms.id')->toArray();
            $reservation->rooms()->detach();
            Rooms::whereIn('id', $roomIds)->update(['status' => 'Available']);
            $reservation->update(['status' => 'Cancelled']);
        });

        $this->dispatch('swal:success', ['message' => 'Reservasi dibatalkan.']);
        $this->loadAvailableRoomTypes();
    }

    public function getReservationsProperty()
    {
        return Reservations::with('rooms')
            ->where('guest_id', Auth::id())
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.user.dashboard', [
            'reservations' => $this->reservations,
            'availableRoomTypes' => $this->availableRoomTypes,
        ]);
    }

    public function getNightsProperty(): int
    {
        if (!$this->check_in_date || !$this->check_out_date) return 0;
        try {
            $in = Carbon::parse($this->check_in_date);
            $out = Carbon::parse($this->check_out_date);
            $diff = $in->diffInDays($out, false);
            return $diff <= 0 ? 1 : $diff; // minimal 1 malam jika sama/hilang
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function getTotalPriceProperty(): int
    {
        $nights = $this->nights;
        if ($nights <= 0) return 0;
        $total = 0;
        foreach ($this->selectedRoomTypes as $type => $count) {
            $count = (int) ($count ?? 0);
            if ($count <= 0) continue;
            $avg = (int) ($this->availableRoomTypes[$type]['avg_price'] ?? 0);
            $total += $count * $avg * $nights;
        }
        return $total;
    }
}
