<?php

namespace App\Livewire\Admin;
use Livewire\Attributes\Layout;

use Livewire\Component;
use App\Models\Reservations;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class AvailabilityCalendar extends Component
{
    #[Title('Availability Calendar')]
    public string $status = '';
    public $roomType = '';
    public string $roomNumber = '';
    /**
     * Fetch and format reservations for FullCalendar.
     */
    public function getCalendarEvents(Request $request)
    {
        // Validate the request has start and end dates
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        // Fetch reservations within the date range, eager loading relationships
        $status = (string) $request->query('status', '');
        $roomTypeId = $request->query('room_type_id');
        $room = (string) $request->query('room', '');

        $reservations = Reservations::with(['rooms', 'guest'])
            ->where('check_in_date', '<=', $request->end)
            ->where('check_out_date', '>=', $request->start)
            ->when($status !== '', function ($q) use ($status) {
                $q->whereRaw('LOWER(status) = ?', [strtolower($status)]);
            })
            ->when(!empty($roomTypeId), function ($q) use ($roomTypeId) {
                $q->whereHas('rooms', function ($qq) use ($roomTypeId) {
                    $qq->where('room_type_id', $roomTypeId);
                });
            })
            ->when($room !== '', function ($q) use ($room) {
                $q->whereHas('rooms', function ($qq) use ($room) {
                    $qq->where('room_number', 'like', "%{$room}%");
                });
            })
            ->get();

        $events = [];
        foreach ($reservations as $reservation) {
            // A reservation can have multiple rooms, create an event for each room
            foreach ($reservation->rooms as $room) {
                $guestName = $reservation->guest->full_name
                    ?? $reservation->guest->username
                    ?? 'Tamu';
                $status = strtolower((string) $reservation->status);
                // Color coding by status
                $colors = [
                    'confirmed' => ['#0d6efd', '#0d6efd'],
                    'checked in' => ['#198754', '#198754'],
                    'checked-in' => ['#198754', '#198754'],
                    'completed' => ['#6c757d', '#6c757d'],
                    'cancelled' => ['#dc3545', '#dc3545'],
                ];
                [$bg, $border] = $colors[$status] ?? ['#1a2e44', '#1a2e44'];
                $events[] = [
                    'title' => 'Kamar ' . $room->room_number,
                    'start' => $reservation->check_in_date->toDateString(),
                    'end' => $reservation->check_out_date->toDateString(), // FullCalendar's end is exclusive
                    'allDay' => true,
                    'backgroundColor' => $bg,
                    'borderColor' => $border,
                    'textColor' => '#fff',
                    'extendedProps' => [
                        'reservation_id' => $reservation->id,
                        'guest' => $guestName,
                        'room' => $room->room_number,
                        'status' => $reservation->status,
                        'check_in' => $reservation->check_in_date->toDateString(),
                        'check_out' => $reservation->check_out_date->toDateString(),
                    ],
                ];
            }
        }

        return response()->json($events);
    }

    public function render()
    {
        return view('livewire.admin.availability-calendar', [
            'roomTypes' => RoomType::orderBy('name')->get(['id','name']),
        ]);
    }

    public function filtersUpdated(): void
    {
        $this->dispatch('calendar:filters-updated',
            status: $this->status,
            room_type_id: $this->roomType ?: '',
            room: $this->roomNumber,
        );
    }
}
