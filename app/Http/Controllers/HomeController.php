<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\HotelGallery;
use App\Models\MenuItem;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomImage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // Optional date range from query for tighter availability stats
        $checkin = request('checkin');
        $checkout = request('checkout');
        $hasValidDates = false;
        $in = $out = null;
        if ($checkin && $checkout) {
            try {
                $inC = Carbon::parse($checkin)->startOfDay();
                $outC = Carbon::parse($checkout)->startOfDay();
                if ($outC->gt($inC)) {
                    $hasValidDates = true;
                    $in = $inC->toDateString();
                    $out = $outC->toDateString();
                }
            } catch (\Throwable $e) {
                $hasValidDates = false;
            }
        }

        // Room type summaries: available count and avg price
        $typeQuery = Room::query()
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
            ->whereNotNull('rooms.room_type_id')
            ->where('rooms.status', 'Available');

        if ($hasValidDates) {
            $typeQuery->whereDoesntHave('reservations', function ($query) use ($in, $out) {
                $query->where(function ($q) use ($in, $out) {
                    $q->where('check_out_date', '>', $in)
                        ->where('check_in_date', '<', $out);
                });
            });
        }

        $cacheKeyTypes = 'home:type_summaries:'.($in ?: 'null').':'.($out ?: 'null');
        $typeRows = Cache::tags(['home'])->remember($cacheKeyTypes, 300, function () use ($typeQuery) {
            return $typeQuery
                ->select(
                    'room_types.id as type_id',
                    'room_types.name as type_name',
                    DB::raw('COUNT(rooms.id) as available_count'),
                    DB::raw('AVG(rooms.price_per_night) as avg_price')
                )
                ->groupBy('room_types.id', 'room_types.name')
                ->orderBy('room_types.name')
                ->get();
        });

        $roomTypeSummaries = $typeRows->map(function ($r) {
            return [
                'id' => (int) $r->type_id,
                'name' => $r->type_name,
                'available' => (int) $r->available_count,
                'avg_price' => (int) round($r->avg_price ?? 0),
            ];
        })->values();

        // Facilities (top 6)
        $facilities = Cache::tags(['home'])->remember('home:facilities:top6', 600, function () {
            return Facility::orderBy('name')->take(6)->get(['name']);
        });

        // Gallery images by category from HotelGallery (separate from room type images)
        $wanted = ['facade', 'facilities', 'public', 'restaurant', 'room'];
        $galleryByCategory = collect($wanted)->mapWithKeys(function ($cat) {
            return [$cat => null];
        });
        $catRows = Cache::tags(['home'])->remember('home:gallery:catRows', 600, function () use ($wanted) {
            return HotelGallery::query()
                ->whereNotNull('category')
                ->whereIn('category', $wanted)
                ->orderByDesc('is_cover')
                ->orderBy('sort_order')
                ->orderByDesc('created_at')
                ->get(['path', 'category']);
        });
        foreach ($catRows as $row) {
            if (! $galleryByCategory[$row->category]) {
                $p = $row->path;
                $galleryByCategory[$row->category] = str_starts_with($p, 'http') ? $p : asset('storage/'.$p);
            }
        }
        // Fill missing categories with latest images
        $latest = Cache::tags(['home'])->remember('home:gallery:latest5', 600, fn () => HotelGallery::latest('created_at')->take(5)->pluck('path'));
        foreach ($galleryByCategory as $k => $v) {
            if (! $v) {
                $p = $latest->shift();
                if ($p) {
                    $galleryByCategory[$k] = str_starts_with($p, 'http') ? $p : asset('storage/'.$p);
                }
            }
        }
        $galleryImages = $galleryByCategory->values();

        // Build room types cards with cover image and additional images
        $typeIds = $roomTypeSummaries->pluck('id')->all();
        $roomImages = Cache::tags(['home'])->remember('home:roomImages:'.md5(json_encode($typeIds)), 600, function () use ($typeIds) {
            return RoomImage::whereIn('room_type_id', $typeIds)
                ->orderByDesc('is_cover')
                ->orderBy('sort_order')
                ->get(['room_type_id', 'path', 'category'])
                ->groupBy('room_type_id');
        });
        $covers = $roomImages->map(function ($g) {
            $p = optional($g->first())->path;

            return $p ? (str_starts_with($p, 'http') ? $p : asset('storage/'.$p)) : null;
        });
        $roomTypeImages = $roomImages->map(function ($g) {
            return $g->take(4)->map(function ($row) {
                $p = $row->path;
                $url = str_starts_with($p, 'http') ? $p : asset('storage/'.$p);

                return ['url' => $url, 'category' => $row->category];
            })->values()->all();
        });
        // Determine popular type by reservations count
        $popularTypeId = \DB::table('reservation_room as rr')
            ->join('rooms as r', 'r.id', '=', 'rr.room_id')
            ->select('r.room_type_id', \DB::raw('COUNT(*) as c'))
            ->groupBy('r.room_type_id')
            ->orderByDesc('c')
            ->value('r.room_type_id');
        $roomTypesCards = collect($roomTypeSummaries)->map(function ($t) use ($covers, $popularTypeId) {
            $t['cover'] = $covers->get($t['id']);
            $t['popular'] = ($popularTypeId && (int) $popularTypeId === (int) $t['id']);

            return $t;
        });

        // Stats
        $stats = [
            'guestCount' => Reservation::count(),
            'roomCount' => Room::count(),
        ];

        $contactEmail = config('mail.from.address');

        // Popular menu items (top up to 6)
        $popularMenus = Cache::tags(['home'])->remember('home:popularMenus:6', 300, function () {
            return MenuItem::with('category')
                ->where(['is_active' => true, 'is_popular' => true])
                ->orderByDesc('updated_at')
                ->take(6)
                ->get(['id', 'name', 'price', 'image', 'menu_category_id', 'is_popular']);
        });

        // Sample menus (non-popular) to showcase variety on homepage
        $excludeIds = $popularMenus->pluck('id');
        $menuSamples = Cache::tags(['home'])->remember('home:menuSamples:6:'.md5($excludeIds->implode(',')), 300, function () use ($excludeIds) {
            return MenuItem::with('category')
                ->where('is_active', true)
                ->when($excludeIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $excludeIds))
                ->latest('updated_at')
                ->take(6)
                ->get(['id', 'name', 'price', 'image', 'menu_category_id', 'is_popular']);
        });

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
