<?php

namespace App\Livewire\Fnb;

use App\Models\FnbOrder;
use Illuminate\Support\Facades\Mail;
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

    public function approvePayment(int $orderId): void
    {
        $o = FnbOrder::findOrFail($orderId);
        $this->authorize('markPaid', $o);
        $o->payment_review_status = 'approved';
        if (! $o->payment_method) {
            $o->payment_method = 'Bank Transfer';
        }
        $o->save();
        $o->markPaid();
        $this->dispatch('swal:success', ['message' => 'Pembayaran disetujui dan ditandai lunas.']);

        try {
            $admin = config('mail.contact_to') ?? config('mail.from.address');
            if ($o->user && $o->user->email) {
                Mail::to($o->user->email)->queue(new \App\Mail\FnbPaymentApprovedMail($o));
            }
            if ($admin) {
                Mail::to($admin)->queue(new \App\Mail\FnbPaymentStatusUpdatedAdminMail($o, 'approved'));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function rejectPayment(int $orderId): void
    {
        $o = FnbOrder::findOrFail($orderId);
        $this->authorize('update', $o);
        $o->payment_review_status = 'rejected';
        $o->save();
        $this->dispatch('swal:info', ['message' => 'Pembayaran ditolak. Minta pelanggan unggah ulang bukti.']);

        try {
            $admin = config('mail.contact_to') ?? config('mail.from.address');
            if ($o->user && $o->user->email) {
                Mail::to($o->user->email)->queue(new \App\Mail\FnbPaymentRejectedMail($o));
            }
            if ($admin) {
                Mail::to($admin)->queue(new \App\Mail\FnbPaymentStatusUpdatedAdminMail($o, 'rejected'));
            }
        } catch (\Throwable $e) {
            report($e);
        }
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
