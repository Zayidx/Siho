<?php

namespace App\Livewire\Admin;

use App\Models\Rooms;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class HousekeepingManagement extends Component
{
    use WithPagination;
    #[Title('Housekeeping Management')]
    public $statusFilter = 'Dirty'; // Default filter
    public $statuses = ['Available', 'Occupied', 'Dirty', 'Cleaning', 'Maintenance'];

    public function render()
    {
        $rooms = Rooms::with('roomType')
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('room_number', 'asc')
            ->paginate(15);

        return view('livewire.admin.housekeeping-management', [
            'rooms' => $rooms,
        ]);
    }

    public function setFilter($status)
    {
        if (in_array($status, $this->statuses) || $status == '') {
            $this->statusFilter = $status;
            $this->resetPage(); // Reset pagination when filter changes
        }
    }

    public function changeStatus($roomId, $newStatus)
    {
        if (!in_array($newStatus, $this->statuses)) {
            return; // Invalid status
        }

        $room = Rooms::findOrFail($roomId);
        
        // Here you could add more complex logic, e.g., 
        // - you can't mark an Occupied room as Available
        // - only Dirty rooms can be marked as Cleaning

        $room->update(['status' => $newStatus]);

        $this->dispatch('swal:success', [
            'message' => "Room #{$room->room_number} status updated to {$newStatus}."
        ]);
    }
}