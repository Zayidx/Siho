<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function booking(Request $request)
    {
        // Legacy route: simply redirect to the booking wizard (no params)
        return redirect()->route('booking.hotel');
    }

    public function bookingConfirm(Request $request, Room $room)
    {
        // Preserve optional dates
        $params = ['room' => $room->id] + $request->only(['checkin', 'checkout']);

        return redirect()->route('booking.hotel', $params);
    }

    public function bookingWizard(Request $request)
    {
        // Backward compatible redirect: keep all query params
        return redirect()->route('booking.hotel', $request->all());
    }

    public function adminRoomImages(Room $room)
    {
        // Legacy admin route: images managed per room type
        return redirect()->route('admin.room-type.images', ['type' => $room->room_type_id]);
    }
}
