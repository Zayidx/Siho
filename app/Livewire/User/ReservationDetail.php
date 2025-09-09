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
use App\Models\RoomType;

#[Layout('components.layouts.user')]
#[Title('Detail Reservasi')]
class ReservationDetail extends Component
{
    use WithFileUploads;
    public Reservations $reservation;
    public $proofFile;
    public $previewUrl = null;

    public function mount(Reservations $reservation)
    {
        abort_unless($reservation->guest_id === Auth::id(), 403);
        $this->reservation = $reservation->load(['rooms','bill.logs']);
    }

    public function render()
    {
        // Ensure relations are loaded
        $this->reservation->loadMissing(['rooms.roomType', 'bill.logs']);

        // Build room type summary
        $groups = $this->reservation->rooms->groupBy('room_type_id');
        $typeIds = $groups->keys()->filter()->values();
        $types = RoomType::with('facilities')->whereIn('id', $typeIds)->get()->keyBy('id');
        $typeSummary = $groups->map(function ($rooms, $typeId) use ($types) {
            $type = $types->get($typeId);
            return [
                'id' => (int) $typeId,
                'name' => $type?->name ?? 'Tipe #'.$typeId,
                'count' => $rooms->count(),
                'avg_price' => (int) round($rooms->avg('price_per_night') ?? 0),
                'capacity' => $type?->capacity,
                'facilities' => $type?->facilities?->map(fn($f)=> ['name'=>$f->name])->values()->all() ?? [],
            ];
        })->values();

        return view('livewire.user.reservation-detail', [
            'typeSummary' => $typeSummary,
        ]);
    }

    public function openPreview()
    {
        $bill = $this->reservation->bill;
        if ($bill && $bill->payment_proof_path) {
            $this->previewUrl = route('user.bills.proof', ['bill' => $bill->id]);
        }
    }

    public function closePreview()
    {
        $this->previewUrl = null;
    }

    protected $validationAttributes = [
        'proofFile' => 'Bukti pembayaran',
    ];

    public function uploadProof()
    {
        $this->validate([
            'proofFile' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ], [
            'proofFile.required' => 'Silakan unggah bukti pembayaran.',
            'proofFile.mimes' => 'Format bukti harus jpg, jpeg, png, atau pdf.',
            'proofFile.max' => 'Ukuran maksimal 4MB.',
        ]);

        $bill = $this->reservation->bill;
        if (! $bill || $bill->paid_at) {
            $this->dispatch('swal:info', ['message' => 'Tagihan tidak tersedia atau sudah dibayar.']);
            return;
        }

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
        \App\Models\PaymentLog::create([
            'bill_id' => $bill->id,
            'user_id' => Auth::id(),
            'action' => 'proof_uploaded',
            'meta' => ['path' => $path],
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
