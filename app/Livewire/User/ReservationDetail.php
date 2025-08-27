<?php

namespace App\Livewire\User;

use App\Models\Reservations;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentProofUploadedMail;

#[Layout('components.layouts.user')]
#[Title('Detail Reservasi')]
class ReservationDetail extends Component
{
    use WithFileUploads;
    public Reservations $reservation;
    public $proofFile;

    public function mount(Reservations $reservation)
    {
        abort_unless($reservation->guest_id === Auth::id(), 403);
        $this->reservation = $reservation->load(['rooms','bill.logs']);
    }

    public function render()
    {
        return view('livewire.user.reservation-detail');
    }

    public function uploadProof()
    {
        $this->validate([
            'proofFile' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $bill = $this->reservation->bill;
        if (! $bill || $bill->paid_at) {
            $this->dispatch('swal:info', ['message' => 'Tagihan tidak tersedia atau sudah dibayar.']);
            return;
        }

        $path = $this->proofFile->store('payment_proofs', 'public');
        $bill->update([
            'payment_method' => 'Bank Transfer',
            'payment_proof_path' => $path,
            'payment_review_status' => 'pending',
            'payment_proof_uploaded_at' => now(),
        ]);
        $this->proofFile = null;
        $this->dispatch('swal:success', ['message' => 'Bukti pembayaran diunggah. Menunggu verifikasi admin.']);

        // Email admin
        try {
            $admin = config('mail.contact_to') ?? config('mail.from.address');
            Mail::to($admin)->queue(new PaymentProofUploadedMail($bill->fresh(['reservation.guest'])));
        } catch (\Throwable $e) { report($e); }
    }
}
