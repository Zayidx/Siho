<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\RoomImage;
use App\Models\Facility;
use App\Models\HotelGallery;
use App\Models\MenuItem;
use App\Models\MenuCategory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Flush homepage caches on content changes
        $flushHome = function () {
            try { Cache::tags(['home'])->flush(); } catch (\Throwable $e) { /* ignore if store doesn't support tags */ }
        };
        Room::saved($flushHome);
        Room::deleted($flushHome);
        RoomType::saved($flushHome);
        RoomType::deleted($flushHome);
        RoomImage::saved($flushHome);
        RoomImage::deleted($flushHome);
        Facility::saved($flushHome);
        Facility::deleted($flushHome);
        HotelGallery::saved($flushHome);
        HotelGallery::deleted($flushHome);
        MenuItem::saved($flushHome);
        MenuItem::deleted($flushHome);

        // Flush menu caches on menu content changes
        $flushMenu = function () {
            try { Cache::tags(['menu'])->flush(); } catch (\Throwable $e) { /* ignore if store doesn't support tags */ }
        };
        MenuItem::saved($flushMenu);
        MenuItem::deleted($flushMenu);
        MenuCategory::saved($flushMenu);
        MenuCategory::deleted($flushMenu);
    }
}
