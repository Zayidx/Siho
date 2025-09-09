<?php

namespace App\Livewire\User;

use App\Mail\InvoicePaidMail;
use App\Mail\PaymentProofUploadedMail;
use App\Models\Bill as BillModel;
use App\Models\PaymentLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.user')]
class Bills extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Title('Tagihan Saya')]
    public $status = '';

    public $search = '';

    public $perPage = 10;

    public $showDetail = false;

    public $selectedBill = null;

    public $proofFile = null;

    public $previewUrl = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = BillModel::with(['reservation.rooms', 'logs'])
            ->whereHas('reservation', fn ($q) => $q->where('guest_id', Auth::id()))
            ->latest();

        if ($this->status === 'paid') {
            // Sudah dibayar
            $query->whereNotNull('paid_at');
        } elseif ($this->status === 'unpaid') {
            // Belum melakukan pembayaran sama sekali (tidak ada bukti & belum mengajukan review)
            $query->whereNull('paid_at')
                ->whereNull('payment_review_status')
                ->whereNull('payment_proof_path');
        } elseif ($this->status === 'pending') {
            // Sudah unggah bukti dan menunggu verifikasi admin
            $query->whereNull('paid_at')->where('payment_review_status', 'pending');
        } elseif ($this->status === 'rejected') {
            // Bukti/permintaan ditolak admin
            $query->whereNull('paid_at')->where('payment_review_status', 'rejected');
        }

        if ($this->search) {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('payment_method', 'like', $term)
                    ->orWhere('notes', 'like', $term);
            });
        }

        return view('livewire.user.bills', [
            'bills' => $query->paginate($this->perPage),
        ]);
    }

    public function view($id)
    {
        $bill = BillModel::with(['reservation.rooms'])
            ->where('id', $id)
            ->whereHas('reservation', fn ($q) => $q->where('guest_id', Auth::id()))
            ->firstOrFail();
        $this->selectedBill = $bill;
        $this->showDetail = true;
    }

    public function closeDetail()
    {
        $this->showDetail = false;
        $this->selectedBill = null;
        $this->reset('proofFile');
    }

    public function openPreviewBill($id)
    {
        $bill = BillModel::where('id', $id)
            ->whereHas('reservation', fn ($q) => $q->where('guest_id', Auth::id()))
            ->firstOrFail();
        if (! $bill->payment_proof_path) {
            return;
        }
        $this->previewUrl = route('user.bills.proof', ['bill' => $bill->id]);
    }

    protected $validationAttributes = [
        'proofFile' => 'Bukti pembayaran',
    ];

    // Deprecated path-based preview: keep no-op for safety

    public function closePreview()
    {
        $this->previewUrl = null;
    }

    public function pay($id, $method = 'Manual')
    {
        $bill = BillModel::where('id', $id)
            ->whereHas('reservation', fn ($q) => $q->where('guest_id', Auth::id()))
            ->firstOrFail();

        if ($bill->paid_at) {
            $this->dispatch('swal:info', ['message' => 'Tagihan sudah dibayar.']);

            return;
        }

        if (strtolower($method) === 'manual') {
            // Wajib ada bukti pembayaran untuk metode manual
            if (! $bill->payment_proof_path) {
                $this->dispatch('swal:error', ['message' => 'Wajib unggah bukti pembayaran terlebih dahulu.']);

                return;
            }
            $bill->update(['payment_method' => 'Manual', 'payment_review_status' => 'pending']);
            PaymentLog::create([
                'bill_id' => $bill->id,
                'user_id' => Auth::id(),
                'action' => 'manual_submit',
                'meta' => ['method' => 'Manual'],
            ]);
            try {
                $admin = config('mail.contact_to') ?? config('mail.from.address');
                Mail::to($admin)->queue(new \App\Mail\PaymentStatusUpdatedAdminMail($bill->fresh(['reservation.guest']), 'pending'));
            } catch (\Throwable $e) {
                report($e);
            }
            $this->dispatch('swal:info', ['message' => 'Pengajuan verifikasi dikirim. Menunggu persetujuan admin.']);
        } else {
            $bill->update(['paid_at' => now(), 'payment_method' => $method, 'payment_review_status' => 'approved']);
            PaymentLog::create([
                'bill_id' => $bill->id,
                'user_id' => Auth::id(),
                'action' => 'online_paid',
                'meta' => ['method' => $method],
            ]);
            $this->dispatch('swal:success', ['message' => 'Pembayaran berhasil dicatat. Terima kasih!']);

            try {
                $output = $this->renderInvoicePdfOutput($bill);
                Mail::to(Auth::user()->email)->queue(new InvoicePaidMail($bill, $output));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        if ($this->showDetail && $this->selectedBill && $this->selectedBill->id === $bill->id) {
            $this->selectedBill = $bill->fresh(['reservation.rooms']);
        }
    }

    public function uploadProof($id)
    {
        $this->validate([
            'proofFile' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ], [
            'proofFile.required' => 'Silakan unggah bukti pembayaran.',
            'proofFile.mimes' => 'Format bukti harus jpg, jpeg, png, atau pdf.',
            'proofFile.max' => 'Ukuran maksimal 4MB.',
        ]);

        $bill = BillModel::where('id', $id)
            ->whereHas('reservation', fn ($q) => $q->where('guest_id', Auth::id()))
            ->firstOrFail();

        if ($bill->paid_at) {
            $this->dispatch('swal:info', ['message' => 'Tagihan sudah dibayar.']);

            return;
        }

        // Cegah unggah ulang bila sudah ada bukti dan belum ditolak admin
        if ($bill->payment_proof_path && $bill->payment_review_status !== 'rejected') {
            $this->dispatch('swal:info', ['message' => 'Bukti pembayaran sudah diunggah. Menunggu verifikasi admin.']);

            return;
        }

        $path = $this->proofFile->store('payment_proofs', 'public');
        $bill->update([
            'payment_method' => 'Bank Transfer',
            'payment_proof_path' => $path,
            'payment_review_status' => 'pending',
            'payment_proof_uploaded_at' => now(),
        ]);

        PaymentLog::create([
            'bill_id' => $bill->id,
            'user_id' => Auth::id(),
            'action' => 'proof_uploaded',
            'meta' => ['path' => $path],
        ]);

        $this->dispatch('swal:success', ['message' => 'Bukti pembayaran diunggah. Menunggu verifikasi admin.']);
        $this->reset('proofFile');

        if ($this->showDetail && $this->selectedBill && $this->selectedBill->id === $bill->id) {
            $this->selectedBill = $bill->fresh(['reservation.rooms']);
        }

        try {
            $admin = config('mail.contact_to') ?? config('mail.from.address');
            Mail::to($admin)->queue(new PaymentProofUploadedMail($bill->fresh(['reservation.guest'])));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function renderInvoicePdfOutput($bill): string
    {
        $html = view('pdf.invoice', [
            'bill' => $bill->load(['reservation.rooms', 'reservation.guest']),
        ])->render();
        $pdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        return $pdf->output();
    }
}
