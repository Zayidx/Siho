<?php

namespace App\Livewire;

use App\Models\Rooms;
use App\Models\RoomType;
use App\Models\Reservations;
use App\Models\Bills;
use App\Models\PaymentLog;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.public')]
#[Title('Booking Wizard')]
class BookingWizard extends Component
{
    use WithFileUploads;
    public $step = 1;
    public $checkin;
    public $checkout;
    public $selectedRoomTypes = [];
    #[Url(as: 'type_id')]
    public $type_id = null;
    public $voucher = '';
    public bool $voucherApplied = false;
    public ?string $voucherMessage = null;
    public $special_requests = '';
    public $createdReservationId = null;
    public $createdBillId = null;
    public $dateInvalid = false;
    public $proofFile = null;

    const TAX_RATE = 0.10; // 10%
    const SERVICE_FEE = 50000; // flat mock
    // Vouchers now from DB (promos table)

    private function isValidDateRange(?string $in, ?string $out): bool
    {
        if (!$in || !$out) return false;
        try {
            $inC = Carbon::parse($in)->startOfDay();
            $outC = Carbon::parse($out)->startOfDay();
        } catch (\Throwable $e) {
            return false;
        }
        if ($inC->lt(Carbon::today())) return false;
        return $outC->gt($inC);
    }

    public function updatedCheckin($value)
    {
        $this->handleDateChange();
    }

    public function updatedCheckout($value)
    {
        $this->handleDateChange();
    }

    private function handleDateChange(): void
    {
        if ($this->checkin && $this->checkout) {
            if ($this->isValidDateRange($this->checkin, $this->checkout)) {
                $this->dateInvalid = false;
                $this->resetErrorBag(['checkin','checkout']);
                if ((int)$this->step === 1) {
                    $this->step = 2;
                }
            } else {
                $this->dateInvalid = true;
                // Opsi A: paksa kembali ke Langkah 1 bila tanggal tidak valid
                if ((int)$this->step > 1) {
                    $this->step = 1;
                    $this->selectedRoomTypes = [];
                    $this->resetErrorBag(['selectedRoomTypes']);
                }
            }
        } else {
            // Incomplete selection clears invalid marker
            $this->dateInvalid = false;
            // Jika tidak lengkap (salah satu kosong) dan sedang di langkah > 1, kembalikan ke Langkah 1
            if ((int)$this->step > 1) {
                $this->step = 1;
                $this->selectedRoomTypes = [];
                $this->resetErrorBag(['selectedRoomTypes']);
            }
        }
    }

    // Note: mount is defined later (merged with existing logic)

    public function getNightsProperty()
    {
        if (!$this->checkin || !$this->checkout) return 0;
        $in = Carbon::parse($this->checkin);
        $out = Carbon::parse($this->checkout);
        $diff = $in->diffInDays($out, false);
        return $diff <= 0 ? 1 : $diff;
    }

    public function getAvailableRoomTypesProperty()
    {
        if (!$this->checkin || !$this->checkout) return collect();
        $in = $this->checkin; $out = $this->checkout;
        $rows = Rooms::query()
            ->join('room_types','rooms.room_type_id','=','room_types.id')
            ->whereNotNull('rooms.room_type_id')
            ->where('rooms.status','Available')
            ->whereDoesntHave('reservations', function ($query) use ($in, $out) {
                $query->where(function ($q) use ($in, $out) {
                    $q->where('check_out_date', '>', $in)
                      ->where('check_in_date', '<', $out);
                });
            })
            ->select('room_types.id as type_id','room_types.name as type_name', \DB::raw('COUNT(rooms.id) as available_count'), \DB::raw('AVG(rooms.price_per_night) as avg_price'))
            ->groupBy('room_types.id','room_types.name')
            ->orderBy('room_types.name')
            ->get();

        $map = $rows->keyBy('type_id')->map(function($r){
            return [
                'name' => $r->type_name,
                'available_count' => (int) $r->available_count,
                'avg_price' => (int) round($r->avg_price ?? 0),
                'facilities' => [],
            ];
        });

        // Load facilities for these room types without mutating Collection items by reference
        $typeIds = $map->keys()->all();
        if (!empty($typeIds)) {
            $types = RoomType::with('facilities')->whereIn('id', $typeIds)->get()->keyBy('id');
            $map = $map->map(function (array $arr, $tid) use ($types) {
                $fac = optional($types->get($tid))->facilities ?? collect();
                $arr['facilities'] = $fac->map(function ($f) {
                    return ['name' => $f->name, 'icon' => $f->icon];
                })->values()->all();
                return $arr;
            });
        }

        return $map;
    }

    public function getSubtotalProperty()
    {
        $n = $this->nights;
        if ($n <= 0) return 0;
        $map = $this->availableRoomTypes;
        $total = 0;
        foreach ((array) $this->selectedRoomTypes as $typeId => $qty) {
            $qty = (int) ($qty ?? 0);
            if ($qty <= 0) continue;
            $avg = (int) ($map[$typeId]['avg_price'] ?? 0);
            $total += $qty * $avg * $n;
        }
        return $total;
    }

    public function getDiscountProperty()
    {
        if (!$this->voucherApplied) return 0;
        $promo = $this->currentPromo();
        if (!$promo) return 0;
        $rate = (float) $promo->discount_rate;
        return (int) round($this->subtotal * $rate);
    }

    public function getTaxProperty()
    {
        return (int) round(($this->subtotal - $this->discount) * self::TAX_RATE);
    }

    public function getTotalProperty()
    {
        return max(0, $this->subtotal - $this->discount + $this->tax + self::SERVICE_FEE);
    }

    public function next()
    {
        $this->step = min(4, $this->step + 1);
    }

    public function back()
    {
        $this->step = max(1, $this->step - 1);
    }

    public function validateStep()
    {
        if ($this->step === 1) {
            $this->validate([
                'checkin' => 'required|date|after_or_equal:today',
                'checkout' => 'required|date|after:checkin',
            ]);
            // Bersihkan error tanggal jika valid
            $this->resetErrorBag(['checkin','checkout']);
        }
        if ($this->step === 2) {
            $sum = collect($this->selectedRoomTypes)->sum(fn($v)=>(int)$v);
            if ($sum <= 0) {
                $this->addError('selectedRoomTypes', 'Pilih minimal satu kamar.');
            } else {
                // Bersihkan error pilihan kamar jika sudah ada pilihan
                $this->resetErrorBag(['selectedRoomTypes']);
            }
        }
    }

    public function goTo($to)
    {
        $this->validateStep();
        if (!$this->getErrorBag()->isEmpty()) return;
        $this->step = $to;
    }

    public function incrementType($typeId)
    {
        $available = (int) ($this->availableRoomTypes[$typeId]['available_count'] ?? 0);
        $current = (int) ($this->selectedRoomTypes[$typeId] ?? 0);
        if ($current < $available) $this->selectedRoomTypes[$typeId] = $current + 1;
    }

    public function decrementType($typeId)
    {
        $current = (int) ($this->selectedRoomTypes[$typeId] ?? 0);
        if ($current > 0) $this->selectedRoomTypes[$typeId] = $current - 1;
    }

    public function selectType($typeId)
    {
        $current = (int) ($this->selectedRoomTypes[$typeId] ?? 0);
        if ($current <= 0) {
            $this->selectedRoomTypes[$typeId] = 1;
        }
        // Tetap di langkah 2 agar pengguna bisa menambah/kurang berdasarkan kebutuhan
        $this->step = 2;
    }

    public function getFullyBookedDatesProperty()
    {
        // Next 45 days quick check (demo): dates with zero available rooms
        $dates = [];
        $start = Carbon::now();
        $end = Carbon::now()->addDays(45);
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $in = $d->toDateString();
            $out = $d->copy()->addDay()->toDateString();
            $availableCount = Rooms::whereDoesntHave('reservations', function ($query) use ($in, $out) {
                $query->where(function ($q) use ($in, $out) {
                    $q->where('check_out_date', '>', $in)
                      ->where('check_in_date', '<', $out);
                });
            })->where('status','Available')->count();
            if ($availableCount === 0) {
                $dates[] = $in;
            }
        }
        return $dates;
    }

    public function confirm()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $this->validateStep();
        $sum = collect($this->selectedRoomTypes)->sum(fn($v)=>(int)$v);
        if ($sum <= 0) {
            $this->addError('selectedRoomTypes', 'Pilih minimal satu kamar.');
            return;
        }
        // Pre-check latest availability before creating transaction
        if (!$this->verifyAvailability()) {
            $this->addError('selectedRoomTypes', 'Ketersediaan kamar berubah. Silakan sesuaikan pilihan Anda.');
            $this->dispatch('swal:error', ['message' => 'Ketersediaan kamar berubah. Perbarui pilihan Anda.']);
            return;
        }
        DB::transaction(function(){
            $reservation = Reservations::create([
                'guest_id' => Auth::id(),
                'check_in_date' => $this->checkin,
                'check_out_date' => $this->checkout,
                'status' => 'Confirmed',
                'special_requests' => $this->special_requests,
            ]);
            $attachIds = [];
            $in = $this->checkin; $out = $this->checkout;
            foreach ((array) $this->selectedRoomTypes as $typeId => $qty) {
                $qty = (int) ($qty ?? 0); if ($qty <= 0) continue;
                $ids = Rooms::where('room_type_id', $typeId)
                    ->where('status','Available')
                    ->whereDoesntHave('reservations', function ($query) use ($in, $out) {
                        $query->where(function ($q) use ($in, $out) {
                            $q->where('check_out_date', '>', $in)
                              ->where('check_in_date', '<', $out);
                        });
                    })
                    ->limit($qty)
                    ->pluck('id')
                    ->toArray();
                if (count($ids) < $qty) abort(422, 'Ketersediaan kamar berubah. Silakan ulangi.');
                $attachIds = array_merge($attachIds, $ids);
            }
            $reservation->rooms()->attach($attachIds);

            // Hitung ulang subtotal/discount/tax secara otoritatif di server
            $nights = $this->getNightsProperty();
            $subtotal = 0;
            $map = $this->getAvailableRoomTypesProperty();
            foreach ((array) $this->selectedRoomTypes as $typeId => $qty) {
                $qty = (int) ($qty ?? 0);
                if ($qty <= 0) continue;
                $avg = (int) ($map[$typeId]['avg_price'] ?? 0);
                $subtotal += $qty * $avg * $nights;
            }
            $discount = 0;
            if ($this->voucherApplied) {
                $code = strtoupper(trim((string) $this->voucher));
                $promo = \App\Models\Promo::activeValid()->whereRaw('UPPER(code) = ?', [$code])->lockForUpdate()->first();
                if ($promo && (is_null($promo->usage_limit) || $promo->used_count < $promo->usage_limit)) {
                    // Pastikan masih berlaku untuk tipe yang dipilih
                    if (empty($promo->apply_room_type_id) || (int) ($this->selectedRoomTypes[$promo->apply_room_type_id] ?? 0) > 0) {
                        $rate = max(0.0, min(1.0, (float) $promo->discount_rate));
                        $discount = (int) round($subtotal * $rate);
                        // Catat penggunaan promo
                        $promo->increment('used_count');
                    }
                }
            }
            $tax = (int) round(max(0, $subtotal - $discount) * self::TAX_RATE);
            $total = max(0, $subtotal - $discount + $tax + self::SERVICE_FEE);

            $bill = Bills::create([
                'reservation_id' => $reservation->id,
                'total_amount' => $total,
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'service_fee_amount' => self::SERVICE_FEE,
                'issued_at' => now(),
                'notes' => $discount > 0 ? ('Promo: '.strtoupper((string)$this->voucher)) : null,
            ]);
            $this->createdReservationId = $reservation->id;
            $this->createdBillId = $bill->id;
        });
        $this->dispatch('swal:info', ['message' => 'Reservasi dibuat. Silakan unggah bukti pembayaran.']);
        // Tetap di langkah 4 agar pengguna bisa langsung unggah bukti
        $this->step = 4;
    }

    private function verifyAvailability(): bool
    {
        $in = $this->checkin; $out = $this->checkout;
        $selected = collect($this->selectedRoomTypes)->filter(fn($v)=> (int)$v>0);
        if ($selected->isEmpty()) return false;
        foreach ($selected as $typeId => $qty) {
            $available = Rooms::where('room_type_id', $typeId)
                ->where('status','Available')
                ->whereDoesntHave('reservations', function ($query) use ($in, $out) {
                    $query->where(function ($q) use ($in, $out) {
                        $q->where('check_out_date', '>', $in)
                          ->where('check_in_date', '<', $out);
                    });
                })
                ->count();
            if ($available < (int)$qty) return false;
        }
        return true;
    }

    public function mount()
    {
        // Initialize dates from query if present
        $this->checkin = request('checkin') ?: $this->checkin;
        $this->checkout = request('checkout') ?: $this->checkout;

        // Preselect by explicit room id (takes precedence)
        if ($roomId = request('room')) {
            $room = Rooms::find((int) $roomId);
            if ($room && $room->room_type_id) {
                $this->selectedRoomTypes[$room->room_type_id] = (($this->selectedRoomTypes[$room->room_type_id] ?? 0) + 1);
            }
            // If a specific room is chosen, jump to summary
            $this->step = 3;
            return;
        }

        // Preselect by room type from URL (?type_id=N)
        $tid = (int) ($this->type_id ?? 0);
        if ($tid > 0 && empty($this->selectedRoomTypes)) {
            // Validate room type exists
            $exists = RoomType::whereKey($tid)->exists();
            if (!$exists) {
                $this->type_id = null;
            } else {
                // If dates provided, ensure availability for the type
                if ($this->checkin && $this->checkout) {
                    $in = $this->checkin; $out = $this->checkout;
                    $available = Rooms::where('room_type_id', $tid)
                        ->where('status','Available')
                        ->whereDoesntHave('reservations', function ($query) use ($in, $out) {
                            $query->where(function ($q) use ($in, $out) {
                                $q->where('check_out_date', '>', $in)
                                  ->where('check_in_date', '<', $out);
                            });
                        })
                        ->count();
                    if ($available <= 0) {
                        // Do not preselect when no availability on chosen dates
                        $this->dispatch('swal:info', ['message' => 'Tipe kamar tidak tersedia pada tanggal yang dipilih']);
                        $this->type_id = null;
                        return;
                    }
                }
                $this->selectedRoomTypes[$tid] = 1;
                // Navigate to selection step so user sees the preselected card
                $this->step = max(2, (int) $this->step);
            }
        }

        // If both dates are provided and valid, and no explicit room preselect, move to step 2
        if ((int)$this->step === 1 && $this->isValidDateRange($this->checkin, $this->checkout)) {
            $this->step = 2;
        }
    }

    public function render()
    {
        $typeNames = RoomType::pluck('name','id');
        $promos = \App\Models\Promo::activeValid()
            ->orderByDesc('discount_rate')
            ->get(['code','name','discount_rate','apply_room_type_id','usage_limit','used_count']);
        return view('livewire.booking-wizard', [
            'availableTypes' => $this->availableRoomTypes,
            'voucherValid' => (bool) $this->currentPromo(),
            'promos' => $promos,
            'typeNames' => $typeNames,
        ]);
    }

    public function applyVoucher(): void
    {
        $code = strtoupper(trim((string) $this->voucher));
        if ($code === '') {
            $this->voucherApplied = false;
            $this->voucherMessage = 'Masukkan kode voucher.';
            $this->dispatch('swal:error', ['message' => 'Masukkan kode voucher.']);
            return;
        }
        $promo = $this->currentPromo();
        if (!$promo) {
            $this->voucherApplied = false;
            $this->voucherMessage = 'Kode tidak valid atau tidak berlaku.';
            $this->dispatch('swal:error', ['message' => 'Kode tidak valid atau tidak berlaku untuk pilihan saat ini.']);
            return;
        }
        $this->voucherApplied = true;
        $this->voucherMessage = 'Kode diterapkan.';
        $this->dispatch('swal:success', ['message' => 'Kode voucher diterapkan.']);
    }

    public function removeVoucher(): void
    {
        $this->voucherApplied = false;
        $this->voucherMessage = null;
        $this->voucher = '';
    }

    private function currentPromo(): ?\App\Models\Promo
    {
        $code = strtoupper(trim((string) $this->voucher));
        if ($code === '') return null;
        $promo = \App\Models\Promo::activeValid()->whereRaw('UPPER(code) = ?', [$code])->first();
        if (!$promo) return null;
        if (!is_null($promo->usage_limit) && $promo->used_count >= $promo->usage_limit) return null;
        if (!empty($promo->apply_room_type_id)) {
            $qty = (int) ($this->selectedRoomTypes[$promo->apply_room_type_id] ?? 0);
            if ($qty <= 0) return null;
        }
        return $promo;
    }

    public function useVoucher(string $code): void
    {
        $this->voucher = strtoupper(trim($code));
        $this->applyVoucher();
    }

    public function payManual()
    {
        if (!$this->createdBillId) return;
        $bill = Bills::find($this->createdBillId);
        if (!$bill || $bill->paid_at) return;
        $bill->update(['payment_method' => 'Manual', 'payment_review_status' => 'pending']);
        PaymentLog::create([
            'bill_id' => $bill->id,
            'user_id' => Auth::id(),
            'action' => 'manual_submit',
            'meta' => ['method' => 'Manual']
        ]);
        try {
            $admin = config('mail.contact_to') ?? config('mail.from.address');
            Mail::to($admin)->queue(new \App\Mail\PaymentStatusUpdatedAdminMail($bill->load('reservation.guest'), 'pending'));
        } catch (\Throwable $e) { report($e); }
        session()->flash('success','Pengajuan verifikasi dikirim. Menunggu persetujuan admin.');
        return redirect()->route('user.reservations');
    }

    public function payOnline()
    {
        if (!$this->createdBillId) return;
        $bill = Bills::find($this->createdBillId);
        if (!$bill || $bill->paid_at) return;
        $bill->update(['payment_method' => 'Online', 'paid_at' => now(), 'payment_review_status' => 'approved']);
        PaymentLog::create([
            'bill_id' => $bill->id,
            'user_id' => Auth::id(),
            'action' => 'online_paid',
            'meta' => ['method' => 'Online']
        ]);
        try {
            $html = view('pdf.invoice', ['bill' => $bill->load(['reservation.rooms','reservation.guest'])])->render();
            $pdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
            $pdf->loadHtml($html);
            $pdf->setPaper('A4','portrait');
            $pdf->render();
            Mail::to(Auth::user()->email)->queue(new \App\Mail\InvoicePaidMail($bill, $pdf->output()));
        } catch (\Throwable $e) { report($e); }
        session()->flash('success','Pembayaran berhasil. Terima kasih!');
        return redirect()->route('user.reservations');
    }

    public function uploadProof()
    {
        if (!$this->createdBillId) return;
        $this->validate([
            'proofFile' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ], [
            'proofFile.required' => 'Silakan unggah bukti pembayaran.',
            'proofFile.mimes' => 'Format bukti harus jpg, jpeg, png, atau pdf.',
            'proofFile.max' => 'Ukuran maksimal 4MB.'
        ]);
        $bill = Bills::find($this->createdBillId);
        if (!$bill || $bill->paid_at) return;
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
            'meta' => ['path' => $path]
        ]);
        try {
            $admin = config('mail.contact_to') ?? config('mail.from.address');
            Mail::to($admin)->queue(new \App\Mail\PaymentProofUploadedMail($bill->fresh(['reservation.guest'])));
            Mail::to($admin)->queue(new \App\Mail\PaymentStatusUpdatedAdminMail($bill->fresh(['reservation.guest']), 'pending'));
        } catch (\Throwable $e) { report($e); }
        $this->reset('proofFile');
        $this->dispatch('swal:success', ['message' => 'Bukti pembayaran diunggah. Menunggu verifikasi admin.']);
        session()->flash('success','Bukti pembayaran diunggah. Menunggu verifikasi admin.');
        return redirect()->route('user.reservations');
    }
}
