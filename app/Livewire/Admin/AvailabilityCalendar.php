<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Reservations;
use Illuminate\Http\Request;

class AvailabilityCalendar extends Component
{
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
        $reservations = Reservations::with(['rooms', 'guest'])
            ->where('check_in_date', '<=', $request->end)
            ->where('check_out_date', '>=', $request->start)
            ->get();

        $events = [];
        foreach ($reservations as $reservation) {
            // A reservation can have multiple rooms, create an event for each room
            foreach ($reservation->rooms as $room) {
                $events[] = [
                    'title' => 'Room ' . $room->room_number . ' (' . $reservation->guest->name . ')',
                    'start' => $reservation->check_in_date->toDateString(),
                    'end' => $reservation->check_out_date->toDateString(), // FullCalendar's end is exclusive
                    'allDay' => true,
                    'backgroundColor' => '#1a2e44', // Example color from your theme
                    'borderColor' => '#1a2e44'
                ];
            }
        }

        return response()->json($events);
    }

    public function render()
    {
        return view('livewire.admin.availability-calendar');
    }
}