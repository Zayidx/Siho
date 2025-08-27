<?php

namespace App\Livewire\Admin;

use App\Models\Bills;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentsReview extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    #[Title('Verifikasi Pembayaran')]
    public $status = 'pending';
    public $search = '';
    public $perPage = 10;
    public $startDate = null;
    public $endDate = null;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatus() { $this->resetPage(); }

    public function render()
    {
        $q = Bills::with(['reservation.guest','logs'])
            ->whereNull('paid_at') // tampilkan semua yang belum dibayar
            ->latest();

        if ($this->status) {
            $q->where('payment_review_status', $this->status);
        }

        if ($this->search) {
            $term = '%'.$this->search.'%';
            $q->where(function ($qq) use ($term) {
                $qq->where('payment_method', 'like', $term)
                   ->orWhere('notes', 'like', $term);
            });
        }

        if ($this->startDate) {
            $q->whereDate('payment_proof_uploaded_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $q->whereDate('payment_proof_uploaded_at', '<=', $this->endDate);
        }

        return view('livewire.admin.payments-review', [
            'items' => $q->paginate($this->perPage),
        ]);
    }

    public function approve($id)
    {
        $bill = Bills::with(['reservation.guest'])->findOrFail($id);
        $bill->update([
            'paid_at' => now(),
            'payment_review_status' => 'approved',
        ]);
        \App\Models\PaymentLog::create([
            'bill_id' => $bill->id,
            'user_id' => auth()->id(),
            'action' => 'admin_approved',
            'meta' => null,
        ]);
        $this->dispatch('swal:success', ['message' => 'Pembayaran disetujui.']);
        // Kabari tamu + lampirkan invoice; kabari admin
        try {
            $html = view('pdf.invoice', [
                'bill' => $bill->load(['reservation.rooms','reservation.guest']),
            ])->render();
            $pdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
            $pdf->loadHtml($html);
            $pdf->setPaper('A4','portrait');
            $pdf->render();
            \Mail::to(optional($bill->reservation->guest)->email)->queue(new \App\Mail\PaymentApprovedMail($bill, $pdf->output()));
            $admin = config('mail.contact_to') ?? config('mail.from.address');
            \Mail::to($admin)->queue(new \App\Mail\PaymentStatusUpdatedAdminMail($bill, 'approved'));
        } catch (\Throwable $e) { report($e); }
    }

    public function reject($id)
    {
        $bill = Bills::with(['reservation.guest'])->findOrFail($id);
        $bill->update([
            'payment_review_status' => 'rejected',
        ]);
        \App\Models\PaymentLog::create([
            'bill_id' => $bill->id,
            'user_id' => auth()->id(),
            'action' => 'admin_rejected',
            'meta' => null,
        ]);
        $this->dispatch('swal:info', ['message' => 'Pembayaran ditolak.']);
        // Kabari tamu + admin
        try {
            \Mail::to(optional($bill->reservation->guest)->email)->queue(new \App\Mail\PaymentRejectedMail($bill));
            $admin = config('mail.contact_to') ?? config('mail.from.address');
            \Mail::to($admin)->queue(new \App\Mail\PaymentStatusUpdatedAdminMail($bill, 'rejected'));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function clearFilters()
    {
        $this->status = 'pending';
        $this->search = '';
        $this->startDate = null;
        $this->endDate = null;
        $this->resetPage();
    }
}
