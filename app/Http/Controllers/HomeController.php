<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\HotelGallery;
use App\Models\MenuItem;
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

        // Gallery images by category from HotelGallery (separate from room type images)
        $wanted = ['facade','facilities','public','restaurant','room'];
        $galleryByCategory = collect($wanted)->mapWithKeys(function($cat){ return [$cat => null]; });
        $catRows = HotelGallery::query()
            ->whereNotNull('category')
            ->whereIn('category', $wanted)
            ->orderByDesc('is_cover')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get(['path','category']);
        foreach ($catRows as $row) {
            if (!$galleryByCategory[$row->category]) {
                $p = $row->path;
                $galleryByCategory[$row->category] = str_starts_with($p, 'http') ? $p : asset('storage/' . $p);
            }
        }
        // Fill missing categories with latest images
        $latest = HotelGallery::latest('created_at')->take(5)->pluck('path');
        foreach ($galleryByCategory as $k => $v) {
            if (!$v) {
                $p = $latest->shift();
                if ($p) {
                    $galleryByCategory[$k] = str_starts_with($p, 'http') ? $p : asset('storage/' . $p);
                }
            }
        }
        $galleryImages = $galleryByCategory->values();

        // Build room types cards with cover image and additional images
        $typeIds = $roomTypeSummaries->pluck('id')->all();
        $roomImages = RoomImage::whereIn('room_type_id', $typeIds)
            ->orderByDesc('is_cover')
            ->orderBy('sort_order')
            ->get(['room_type_id','path','category'])
            ->groupBy('room_type_id');
        $covers = $roomImages->map(function($g){
            $p = optional($g->first())->path;
            return $p ? (str_starts_with($p,'http') ? $p : asset('storage/'.$p)) : null;
        });
        $roomTypeImages = $roomImages->map(function($g){
            return $g->take(4)->map(function($row){
                $p = $row->path;
                $url = str_starts_with($p,'http') ? $p : asset('storage/'.$p);
                return ['url' => $url, 'category' => $row->category];
            })->values()->all();
        });
        // Determine popular type by reservations count
        $popularTypeId = \DB::table('reservation_room as rr')
            ->join('rooms as r','r.id','=','rr.room_id')
            ->select('r.room_type_id', \DB::raw('COUNT(*) as c'))
            ->groupBy('r.room_type_id')
            ->orderByDesc('c')
            ->value('r.room_type_id');
        $roomTypesCards = collect($roomTypeSummaries)->map(function($t) use ($covers, $popularTypeId){
            $t['cover'] = $covers->get($t['id']);
            $t['popular'] = ($popularTypeId && (int)$popularTypeId === (int)$t['id']);
            return $t;
        });

        // Stats
        $stats = [
            'guestCount' => Reservations::count(),
            'roomCount' => Rooms::count(),
        ];

        $contactEmail = config('mail.from.address');

        // Popular menu items (top up to 6)
        $popularMenus = MenuItem::with('category')
            ->where(['is_active'=>true,'is_popular'=>true])
            ->orderByDesc('updated_at')
            ->take(6)
            ->get(['id','name','price','image','menu_category_id','is_popular']);

        // Sample menus (non-popular) to showcase variety on homepage
        $excludeIds = $popularMenus->pluck('id');
        $menuSamples = MenuItem::with('category')
            ->where('is_active', true)
            ->when($excludeIds->isNotEmpty(), fn($q) => $q->whereNotIn('id', $excludeIds))
            ->latest('updated_at')
            ->take(6)
            ->get(['id','name','price','image','menu_category_id','is_popular']);

        return view('welcome', [
            'roomTypeSummaries' => $roomTypeSummaries,
            'facilities' => $facilities,
            'galleryImages' => $galleryImages,
            'galleryByCategory' => $galleryByCategory,
            'stats' => $stats,
            'roomTypesCards' => $roomTypesCards,
            'roomTypeCovers' => $covers,
            'roomTypeImages' => $roomTypeImages,
            'contactEmail' => $contactEmail,
            'popularMenus' => $popularMenus,
            'menuSamples' => $menuSamples,
        ]);
    }
}
