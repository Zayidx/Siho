<?php

namespace App\Livewire\Fnb;

use App\Models\FnbOrder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Kasir F&B')]
class CashierDashboard extends Component
{
    use AuthorizesRequests, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $statusFilter = '';

    public function setStatus(int $orderId, string $status)
    {
        if (! in_array($status, FnbOrder::ALLOWED_STATUSES, true)) {
            return;
        }
        $o = FnbOrder::findOrFail($orderId);
        $this->authorize('update', $o);
        $o->setStatusSafe($status);
    }

    public function markPaid(int $orderId)
    {
        $o = FnbOrder::findOrFail($orderId);
        $this->authorize('markPaid', $o);
        $o->markPaid();
    }

    public function render()
    {
        $q = FnbOrder::with(['items.item', 'user'])->latest();
        if ($this->statusFilter) {
            $q->where('status', $this->statusFilter);
        }
        $orders = $q->paginate(10);

        return view('livewire.fnb.cashier-dashboard', compact('orders'));
    }
}
