<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Reservations;
use App\Models\RoomImage;
use App\Models\Rooms;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // Room type summaries: available count and avg price
        $typeRows = Rooms::query()
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
            ->whereNotNull('rooms.room_type_id')
            ->where('rooms.status', 'Available')
            ->select(
                'room_types.id as type_id',
                'room_types.name as type_name',
                DB::raw('COUNT(rooms.id) as available_count'),
                DB::raw('AVG(rooms.price_per_night) as avg_price')
            )
            ->groupBy('room_types.id', 'room_types.name')
            ->orderBy('room_types.name')
            ->get();

        $roomTypeSummaries = $typeRows->map(function ($r) {
            return [
                'id' => (int) $r->type_id,
                'name' => $r->type_name,
                'available' => (int) $r->available_count,
                'avg_price' => (int) round($r->avg_price ?? 0),
            ];
        })->values();

        // Facilities (top 6)
        $facilities = Facility::orderBy('name')->take(6)->get(['name']);

        // Gallery images: latest 3; fallback handled in view
        $galleryImages = RoomImage::query()
            ->latest('created_at')
            ->take(3)
            ->get(['path'])
            ->pluck('path')
            ->map(function ($p) {
                return str_starts_with($p, 'http') ? $p : asset('storage/' . $p);
            })
            ->values();

        // Stats
        $stats = [
            'guestCount' => Reservations::count(),
            'roomCount' => Rooms::count(),
        ];

        $contactEmail = config('mail.from.address');

        return view('welcome', compact('roomTypeSummaries','facilities','galleryImages','stats','contactEmail'));
    }
}
