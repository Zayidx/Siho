<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Room;

class ReservationService
{
    /**
     * Sync rooms for a reservation based on selected room types and update room statuses.
     *
     * @param  array<int,int>  $selectedRoomTypes  Map of room_type_id => count
     * @return array{attached: array<int>, detached: array<int>, synced: array<int,int>}
     */
    public function syncRoomsForReservation(Reservation $reservation, array $selectedRoomTypes): array
    {
        $roomIdsToSync = [];

        foreach ($selectedRoomTypes as $typeId => $count) {
            if ($count > 0) {
                $availableRoomIds = Room::where('room_type_id', $typeId)
                    ->where(function ($query) use ($reservation) {
                        $query->where('status', 'Available')
                            ->orWhereHas('reservations', fn ($q) => $q->where('reservations.id', $reservation->id));
                    })
                    ->take($count)
                    ->pluck('id')
                    ->toArray();

                $roomIdsToSync = array_merge($roomIdsToSync, $availableRoomIds);
            }
        }

        $syncResult = $reservation->rooms()->sync($roomIdsToSync);

        // Update statuses for newly attached/detached rooms
        if (! empty($syncResult['attached'])) {
            Room::whereIn('id', $syncResult['attached'])->update(['status' => 'Occupied']);
        }
        if (! empty($syncResult['detached'])) {
            Room::whereIn('id', $syncResult['detached'])->update(['status' => 'Available']);
        }

        return $syncResult;
    }
}
