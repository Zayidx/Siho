<?php

namespace App\Livewire\User;

use App\Models\Reservation as ReservationModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.user')]
#[Title('Reservasi Saya')]
class Reservations extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $perPage = 10;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $q = ReservationModel::with('rooms')
            ->where('guest_id', Auth::id())
            ->orderByDesc('created_at');
        if ($this->search) {
            $term = '%'.$this->search.'%';
            $q->where(function ($qq) use ($term) {
                $qq->where('status', 'like', $term)
                    ->orWhere('special_requests', 'like', $term);
            });
        }

        return view('livewire.user.reservations', [
            'items' => $q->paginate($this->perPage),
        ]);
    }
}
