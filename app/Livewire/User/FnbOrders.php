<?php

namespace App\Livewire\User;

use App\Models\FnbOrder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('components.layouts.user')]
#[Title('Pesanan Makanan Saya')]
class FnbOrders extends Component
{
    use WithPagination, AuthorizesRequests;
    protected $paginationTheme = 'bootstrap';

    public function cancel(int $id): void
    {
        $o = FnbOrder::where('user_id', Auth::id())->findOrFail($id);
        $this->authorize('cancel', $o);
        if ($o->isCancelable()) {
            $o->setStatusSafe(FnbOrder::STATUS_CANCELLED);
            $this->dispatch('swal:success', ['message' => 'Pesanan dibatalkan.']);
        } else {
            $this->dispatch('swal:error', ['message' => 'Pesanan tidak dapat dibatalkan.']);
        }
    }

    public function render()
    {
        $orders = FnbOrder::with('items.item')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);
        return view('livewire.user.fnb-orders', compact('orders'));
    }
}
