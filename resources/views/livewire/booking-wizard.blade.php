<div class="container py-5">
    @include('components.public.breadcrumbs', ['items' => [
        ['label' => 'Beranda', 'url' => url('/')],
        ['label' => 'Booking']
    ]])
    <h3 class="mb-3">Booking Wizard</h3>
    <div class="card">
        <div class="card-body">
            <ul class="nav nav-pills mb-3">
                <li class="nav-item"><a class="nav-link {{ $step===1?'active':'' }}" href="#" wire:click.prevent="goTo(1)">1. Tanggal</a></li>
                <li class="nav-item"><a class="nav-link {{ $step===2?'active':'' }}" href="#" wire:click.prevent="goTo(2)">2. Pilih Kamar</a></li>
                <li class="nav-item"><a class="nav-link {{ $step===3?'active':'' }}" href="#" wire:click.prevent="goTo(3)">3. Ringkasan</a></li>
                <li class="nav-item"><a class="nav-link {{ $step===4?'active':'' }}" href="#" wire:click.prevent="goTo(4)">4. Pembayaran</a></li>
            </ul>

            @if($step===1)
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Check-in</label>
                        <input type="date" class="form-control @error('checkin') is-invalid @enderror" wire:model.defer="checkin" min="{{ now()->toDateString() }}">
                        @error('checkin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Check-out</label>
                        <input type="date" class="form-control @error('checkout') is-invalid @enderror" wire:model.defer="checkout" min="{{ $checkin ?: now()->toDateString() }}">
                        @error('checkout') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <!-- Hilangkan input jumlah tamu -->
                    <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-primary" wire:click="goTo(2)">Lanjut</button>
                    </div>
                    <div class="col-12">
                        <div class="mt-3 p-2 border rounded">
                            <div class="text-muted small mb-1">Kalender Ketersediaan (45 hari ke depan)</div>
                            @php
                                $start = \Carbon\Carbon::now()->startOfMonth();
                                $months = [$start->copy(), $start->copy()->addMonth()];
                                $fb = array_flip($this->fullyBookedDates);
                            @endphp
                            <div class="row g-2">
                                @foreach($months as $m)
                                    @php($first = $m->copy()->startOfMonth())
                                    @php($last = $m->copy()->endOfMonth())
                                    @php($startDow = (int)$first->format('N'))
                                    <div class="col-6">
                                        <div class="border rounded p-2 small">
                                            <div class="text-center fw-semibold mb-1">{{ $m->translatedFormat('F Y') }}</div>
                                            <div class="d-grid" style="grid-template-columns: repeat(7, 1fr); gap: 4px;">
                                                @foreach(['S','S','R','K','J','S','M'] as $w)
                                                    <div class="text-center text-muted">{{ $w }}</div>
                                                @endforeach
                                                @for($i=1;$i<$startDow;$i++)
                                                    <div></div>
                                                @endfor
                                                @for($d=1;$d<=$last->day;$d++)
                                                    @php($dateStr = $m->copy()->day($d)->format('Y-m-d'))
                                                    @php($isFull = isset($fb[$dateStr]))
                                                    <div class="text-center {{ $isFull ? 'bg-danger text-white' : 'bg-light' }} rounded" title="{{ $dateStr }}">{{ $d }}</div>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($step===2)
                <div class="mb-2 text-muted">Lama menginap: <strong>{{ $this->nights }}</strong> malam</div>
                <div class="row g-3">
                    @forelse($availableTypes as $typeId => $info)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100">
                                <div class="card-body d-flex flex-column">
                                    <div class="mb-2">
                                        <h6 class="mb-0">{{ $info['name'] }}</h6>
                                        <small class="text-muted">Tersedia: {{ $info['available_count'] }}</small>
                                    </div>
                                    <div class="mb-2">Rp {{ number_format($info['avg_price'],0,',','.') }} <small class="text-muted">/ malam</small></div>
                                    @if(!empty($info['facilities']))
                                        <div class="mb-3">
                                            @foreach($info['facilities'] as $f)
                                                <span class="badge text-bg-light border me-1 mb-1">
                                                    @if(!empty($f['icon']))<i class="{{ $f['icon'] }} me-1"></i>@endif
                                                    {{ $f['name'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="mt-auto">
                                        <div class="btn-group" role="group" aria-label="counter">
                                            <button type="button" class="btn btn-outline-secondary" wire:click="decrementType('{{ $typeId }}')">-</button>
                                            <button type="button" class="btn btn-light" disabled style="min-width:60px;">{{ $selectedRoomTypes[$typeId] ?? 0 }}</button>
                                            <button type="button" class="btn btn-outline-secondary" wire:click="incrementType('{{ $typeId }}')">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="alert alert-warning">Tidak ada kamar tersedia pada tanggal tersebut.</div></div>
                    @endforelse
                </div>
                @error('selectedRoomTypes') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                <div class="d-flex justify-content-between mt-3">
                    <button class="btn btn-outline-secondary" wire:click="back">Kembali</button>
                    <button class="btn btn-primary" wire:click="goTo(3)">Lanjut</button>
                </div>
            @endif

            @if($step===3)
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li><strong>Check-in:</strong> {{ $checkin }}</li>
                            <li><strong>Check-out:</strong> {{ $checkout }}</li>
                            <li><strong>Malam:</strong> {{ $this->nights }}</li>
                            <li><strong>Pilihan:</strong>
                                @php($sel = collect($selectedRoomTypes ?? []))
                                @if($sel->sum()>0)
                                    <ul class="mb-0">
                                        @foreach($sel as $tId=>$qty)
                                            @if($qty>0)
                                                <li>{{ $availableTypes[$tId]['name'] ?? ('Tipe #'.$tId) }} &times; {{ $qty }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @else - @endif
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Permintaan Khusus</label>
                        <textarea class="form-control" rows="3" wire:model.defer="special_requests"></textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button class="btn btn-outline-secondary" wire:click="back">Kembali</button>
                    <button class="btn btn-primary" wire:click="goTo(4)">Lanjut</button>
                </div>
            @endif

            @if($step===4)
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Kode Voucher</label>
                        <input type="text" class="form-control" placeholder="Contoh: HEMAT10" wire:model.debounce.300ms="voucher">
                    </div>
                </div>
                <div class="mt-3 p-3 border rounded bg-light">
                    <div class="d-flex justify-content-between"><span>Subtotal</span><strong>Rp {{ number_format($this->subtotal,0,',','.') }}</strong></div>
                    <div class="d-flex justify-content-between"><span>Diskon</span><strong>- Rp {{ number_format($this->discount,0,',','.') }}</strong></div>
                    <div class="d-flex justify-content-between"><span>Pajak (10%)</span><strong>Rp {{ number_format($this->tax,0,',','.') }}</strong></div>
                    <div class="d-flex justify-content-between"><span>Biaya Layanan</span><strong>Rp {{ number_format(50000,0,',','.') }}</strong></div>
                    <hr>
                    <div class="d-flex justify-content-between"><span>Total</span><strong class="text-primary">Rp {{ number_format($this->total,0,',','.') }}</strong></div>
                </div>
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="agree" required>
                    <label class="form-check-label" for="agree">Saya setuju dengan kebijakan pembatalan.</label>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button class="btn btn-outline-secondary" wire:click="back">Kembali</button>
                    <button class="btn btn-success" onclick="if(!document.getElementById('agree').checked){event.preventDefault();alert('Harap setujui kebijakan.');}else{Livewire.find('{{ $this->getId() }}').call('confirm')}">Konfirmasi Pemesanan</button>
                </div>
            @endif

            @if($step===5)
                <div class="alert alert-info">Reservasi berhasil dibuat. Pilih metode pembayaran di bawah ini:</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column">
                                <h5>Bayar Online (Mock)</h5>
                                <p class="text-muted small">Instan disetujui. Invoice dikirim ke email Anda.</p>
                                <div class="mt-auto">
                                    <button class="btn btn-primary" wire:click="payOnline">Bayar Sekarang</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column">
                                <h5>Transfer Manual</h5>
                                <p class="text-muted small">Ajukan verifikasi. Anda dapat unggah bukti di halaman Tagihan/Detail Reservasi.</p>
                                <div class="mt-auto">
                                    <button class="btn btn-outline-primary" wire:click="payManual">Ajukan Verifikasi</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const disabled = @json($this->fullyBookedDates);
            function guard(input){
                input?.addEventListener('change', () => {
                    if (disabled.includes(input.value)){
                        alert('Tanggal tersebut penuh. Silakan pilih tanggal lain.');
                        input.value = '';
                    }
                });
            }
            guard(document.querySelector('input[type=date][wire\\:model\\.defer="checkin"]'));
            guard(document.querySelector('input[type=date][wire\\:model\\.defer="checkout"]'));
        });
    </script>
</div>
