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
use Livewire\Component;

#[Layout('components.layouts.public')]
#[Title('Booking Wizard')]
class BookingWizard extends Component
{
    public $step = 1;
    public $checkin;
    public $checkout;
    public $selectedRoomTypes = [];
    public $voucher = '';
    public $special_requests = '';
    public $createdReservationId = null;
    public $createdBillId = null;

    const TAX_RATE = 0.10; // 10%
    const SERVICE_FEE = 50000; // flat mock
    // Vouchers now from DB (promos table)

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

        // Load facilities for these room types
        $typeIds = $map->keys()->all();
        if (!empty($typeIds)) {
            $types = RoomType::with('facilities')->whereIn('id', $typeIds)->get()->keyBy('id');
            foreach ($typeIds as $tid) {
                $fac = $types[$tid]->facilities ?? collect();
                $map[$tid]['facilities'] = $fac->map(function($f){
                    return ['name' => $f->name, 'icon' => $f->icon];
                })->values()->all();
            }
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
        $code = strtoupper(trim((string)$this->voucher));
        if (!$code) return 0;
        $promo = \App\Models\Promo::activeValid()->whereRaw('UPPER(code) = ?', [$code])->first();
        if (!$promo) return 0;
        if (!is_null($promo->usage_limit) && $promo->used_count >= $promo->usage_limit) return 0;
        if (!empty($promo->apply_room_type_id)) {
            $qty = (int) ($this->selectedRoomTypes[$promo->apply_room_type_id] ?? 0);
            if ($qty <= 0) return 0;
        }
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
        }
        if ($this->step === 2) {
            $sum = collect($this->selectedRoomTypes)->sum(fn($v)=>(int)$v);
            if ($sum <= 0) $this->addError('selectedRoomTypes', 'Pilih minimal satu kamar.');
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
            Rooms::whereIn('id', $attachIds)->update(['status' => 'Occupied']);
            $bill = Bills::create([
                'reservation_id' => $reservation->id,
                'total_amount' => $this->total,
                'subtotal_amount' => $this->subtotal,
                'discount_amount' => $this->discount,
                'tax_amount' => $this->tax,
                'service_fee_amount' => self::SERVICE_FEE,
                'issued_at' => now(),
            ]);
            $this->createdReservationId = $reservation->id;
            $this->createdBillId = $bill->id;
            // increment promo usage if applied
            $code = strtoupper(trim((string)$this->voucher));
            if ($code) {
                $promo = \App\Models\Promo::activeValid()->whereRaw('UPPER(code) = ?', [$code])->lockForUpdate()->first();
                if ($promo && (is_null($promo->usage_limit) || $promo->used_count < $promo->usage_limit)) {
                    $promo->increment('used_count');
                }
            }
        });
        $this->dispatch('swal:info', ['message' => 'Reservasi dibuat. Silakan pilih metode pembayaran.']);
        $this->step = 5; // go to payment step
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
        $this->checkin = request('checkin') ?: $this->checkin;
        $this->checkout = request('checkout') ?: $this->checkout;
        if ($roomId = request('room')) {
            $room = Rooms::find((int) $roomId);
            if ($room && $room->room_type_id) {
                $this->selectedRoomTypes[$room->room_type_id] = (($this->selectedRoomTypes[$room->room_type_id] ?? 0) + 1);
            }
            $this->step = 3; // skip to summary if room preselected
        }
    }

    public function render()
    {
        return view('livewire.booking-wizard', [
            'availableTypes' => $this->availableRoomTypes,
        ]);
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
}
