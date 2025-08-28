<div class="max-w-6xl mx-auto py-10 px-4">
    @include('components.public.breadcrumbs', ['items' => [
        ['label' => 'Beranda', 'url' => url('/')],
        ['label' => 'Booking Hotel']
    ]])

    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2 mb-6">Booking Hotel</h1>

    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
        <div class="p-4 md:p-6">
            <!-- Steps -->
            <div class="mb-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    @php
                        $steps = [1=>'Tanggal',2=>'Pilih Kamar',3=>'Ringkasan',4=>'Pembayaran'];
                    @endphp
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 w-full">
                        @foreach($steps as $i=>$label)
                            <button type="button" wire:click.prevent="goTo({{ $i }})" class="w-full text-left sm:text-center px-3 py-2 rounded-lg border transition
                                {{ $step===$i ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                                <span class="inline-flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-semibold rounded-full
                                        {{ $step===$i ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">{{ $i }}</span>
                                    <span class="font-medium">{{ $label }}</span>
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            @if($step===1)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Check-in</label>
                        <input type="date" wire:model.defer="checkin" min="{{ now()->toDateString() }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500" />
                        @error('checkin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Check-out</label>
                        <input type="date" wire:model.defer="checkout" min="{{ $checkin ?: now()->toDateString() }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500" />
                        @error('checkout') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    @if($dateInvalid)
                        <div class="md:col-span-2">
                            <div class="mt-1 rounded-md bg-yellow-50 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 text-sm px-3 py-2">
                                Rentang tanggal tidak valid. Pastikan check-out setelah check-in dan tidak di masa lalu.
                            </div>
                        </div>
                    @endif

                    <div class="md:col-span-2 flex justify-end">
                        <button class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500" wire:click="goTo(2)">Lanjut</button>
                    </div>

                    
                </div>
            @endif

            @if($step===2)
                @php $canNext = collect($selectedRoomTypes ?? [])->sum() > 0; @endphp
                <div class="mb-2 text-sm text-gray-600 dark:text-gray-300">Lama menginap: <strong>{{ $this->nights }}</strong> malam</div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($availableTypes as $typeId => $info)
                        @php $isSelected = (int)($selectedRoomTypes[$typeId] ?? 0) > 0; @endphp
                        <div class="rounded-xl border {{ $isSelected ? 'border-blue-600 ring-1 ring-blue-200 dark:ring-blue-900/40' : 'border-gray-200 dark:border-gray-800' }} bg-white dark:bg-gray-900 p-4 flex flex-col">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $info['name'] }}</h3>
                                    <p class="text-xs text-gray-500">Tersedia: {{ $info['available_count'] }}</p>
                                </div>
                                @if($isSelected)
                                    <span class="inline-flex items-center text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">Dipilih</span>
                                @endif
                            </div>
                            <div class="mt-2 text-gray-800 dark:text-gray-200">Rp {{ number_format($info['avg_price'],0,',','.') }} <span class="text-xs text-gray-500">/ malam</span></div>
                            @if(!empty($info['facilities']))
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach($info['facilities'] as $f)
                                        <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                            {{ $f['name'] }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="mt-auto pt-3 flex items-center justify-between">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-md border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800" wire:click="decrementType('{{ $typeId }}')">-</button>
                                    <span class="inline-flex items-center justify-center w-12 h-8 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">{{ $selectedRoomTypes[$typeId] ?? 0 }}</span>
                                    <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-md border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800" wire:click="incrementType('{{ $typeId }}')">+</button>
                                </div>
                                <button type="button" class="inline-flex items-center px-3 py-1.5 rounded-md border border-blue-600 text-blue-600 hover:bg-blue-50" wire:click="selectType('{{ $typeId }}')">Pilih</button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full">
                            <div class="rounded-md bg-yellow-50 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 text-sm px-3 py-2">Tidak ada kamar tersedia pada tanggal tersebut.</div>
                        </div>
                    @endforelse
                </div>
                @error('selectedRoomTypes') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                <div class="mt-4 flex items-center justify-between">
                    <button class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800" wire:click="back">Kembali</button>
                    <div class="text-right">
                        <button class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" wire:click="goTo(3)" @disabled(!$canNext)>Lanjut</button>
                        @unless($canNext)
                            <div class="text-xs text-gray-500 mt-1">Pilih minimal satu kamar untuk melanjutkan.</div>
                        @endunless
                    </div>
                </div>
            @endif

            @if($step===3)
                @php
                    $sel = collect($selectedRoomTypes ?? []);
                    $selSum = $sel->sum();
                    $canNext3 = $selSum > 0 && !$dateInvalid;
                @endphp
                <div class="rounded-md bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3 py-2 mb-4 flex items-center justify-between">
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-800 dark:text-gray-200">
                        <strong>Ringkasan Singkat:</strong>
                        <span class="ms-1">{{ $checkin }} → {{ $checkout }}</span>
                        <span class="ms-3">Malam: <strong>{{ $this->nights }}</strong></span>
                        <span class="ms-3">Kamar dipilih: <strong>{{ $selSum }}</strong></span>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <button class="inline-flex items-center px-3 py-1.5 rounded-md border border-blue-600 text-blue-600 hover:bg-blue-50" wire:click="goTo(1)">Ubah Tanggal</button>
                        <button class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800" wire:click="goTo(2)">Ubah Pilihan Kamar</button>
                        @unless($canNext3)
                            <span class="text-xs text-red-600">Lengkapi pilihan kamar atau perbaiki tanggal untuk lanjut.</span>
                        @endunless
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <ul class="space-y-1 text-sm text-gray-800 dark:text-gray-200">
                            <li><strong>Check-in:</strong> {{ $checkin }}</li>
                            <li><strong>Check-out:</strong> {{ $checkout }}</li>
                            <li><strong>Malam:</strong> {{ $this->nights }}</li>
                            <li><strong>Pilihan:</strong>
                                @if($sel->sum()>0)
                                    <ul class="list-disc ml-5">
                                        @foreach($sel as $tId=>$qty)
                                            @if($qty>0)
                                                <li>{{ $availableTypes[$tId]['name'] ?? ('Tipe #'.$tId) }} &times; {{ $qty }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @else
                                    -
                                @endif
                            </li>
                        </ul>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Permintaan Khusus</label>
                        <textarea rows="3" wire:model.defer="special_requests"
                                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"></textarea>
                        <div class="mt-3 p-3 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800 text-sm">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Estimasi Biaya</div>
                            <div class="flex items-center justify-between"><span>Subtotal</span><strong>Rp {{ number_format($this->subtotal,0,',','.') }}</strong></div>
                            <div class="flex items-center justify-between"><span>Diskon</span><strong>- Rp {{ number_format($this->discount,0,',','.') }}</strong></div>
                            <div class="flex items-center justify-between"><span>Pajak (10%)</span><strong>Rp {{ number_format($this->tax,0,',','.') }}</strong></div>
                            <div class="flex items-center justify-between"><span>Biaya Layanan</span><strong>Rp {{ number_format(50000,0,',','.') }}</strong></div>
                            <div class="my-2 border-t border-gray-200 dark:border-gray-700"></div>
                            <div class="flex items-center justify-between"><span>Total Estimasi</span><strong class="text-blue-600">Rp {{ number_format($this->total,0,',','.') }}</strong></div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <button class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800" wire:click="back">Kembali</button>
                    <div class="text-right">
                        <button class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" wire:click="goTo(4)" @disabled(!$canNext3)>Lanjut</button>
                        @unless($canNext3)
                            <div class="text-xs text-gray-500 mt-1">Pilih minimal satu kamar dan pastikan tanggal valid.</div>
                        @endunless
                    </div>
                </div>
            @endif

            @if($step===4)
                @php
                    $sel = collect($selectedRoomTypes ?? []);
                    $selSum = $sel->sum();
                    $canConfirm = ($selSum > 0) && !$dateInvalid && ($this->nights > 0);
                @endphp
                <div class="rounded-md bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3 py-2 mb-4 flex items-center justify-between">
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-800 dark:text-gray-200">
                        <strong>Ringkasan Singkat:</strong>
                        <span class="ms-1">{{ $checkin }} → {{ $checkout }}</span>
                        <span class="ms-3">Malam: <strong>{{ $this->nights }}</strong></span>
                        <span class="ms-3">Kamar dipilih: <strong>{{ $selSum }}</strong></span>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <button class="inline-flex items-center px-3 py-1.5 rounded-md border border-blue-600 text-blue-600 hover:bg-blue-50" wire:click="goTo(1)">Ubah Tanggal</button>
                        <button class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800" wire:click="goTo(2)">Ubah Pilihan Kamar</button>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode Voucher</label>
                        <div class="mt-1 flex gap-2">
                            <input type="text" placeholder="Contoh: HEMAT10" wire:model.defer="voucher"
                                   class="flex-1 block rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500" />
                            <button type="button" class="inline-flex items-center px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700" wire:click="applyVoucher">Gunakan Kode</button>
                            @if($voucherApplied)
                                <button type="button" class="inline-flex items-center px-3 py-2 rounded-md border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800" wire:click="removeVoucher">Hapus</button>
                            @endif
                        </div>
                        @if($voucherMessage)
                            <div class="mt-1 text-xs {{ $voucherValid && $voucherApplied ? 'text-green-600' : 'text-red-600' }}">{{ $voucherMessage }}</div>
                        @endif
                        @if($voucherApplied && !$voucherValid)
                            <div class="mt-1 text-xs text-yellow-600">Kode tidak lagi berlaku untuk pilihan kamar saat ini.</div>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Promo Tersedia</label>
                        <div class="mt-1 flex flex-wrap gap-2">
                            @forelse($promos as $p)
                                <button type="button" class="text-xs inline-flex items-center gap-1 px-2 py-1 rounded-full border border-blue-600 text-blue-600 hover:bg-blue-50"
                                        wire:click="useVoucher('{{ $p->code }}')">
                                    <span class="font-semibold">{{ $p->code }}</span>
                                    <span>({{ $p->name }}, {{ (int)($p->discount_rate*100) }}%)</span>
                                    @if($p->apply_room_type_id)
                                        <span class="text-gray-500">· {{ $typeNames[$p->apply_room_type_id] ?? ('Tipe #'.$p->apply_room_type_id) }}</span>
                                    @endif
                                </button>
                            @empty
                                <span class="text-xs text-gray-500">Tidak ada promo aktif.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="mt-3 p-3 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800 text-sm">
                    <div class="flex items-center justify-between"><span>Subtotal</span><strong>Rp {{ number_format($this->subtotal,0,',','.') }}</strong></div>
                    <div class="flex items-center justify-between"><span>Diskon @if($voucherApplied && $voucherValid)<span class="text-xs text-gray-500">(Voucher)</span>@endif</span><strong>- Rp {{ number_format($this->discount,0,',','.') }}</strong></div>
                    <div class="flex items-center justify-content-between"><span>Pajak (10%)</span><strong>Rp {{ number_format($this->tax,0,',','.') }}</strong></div>
                    <div class="flex items-center justify-between"><span>Biaya Layanan</span><strong>Rp {{ number_format(50000,0,',','.') }}</strong></div>
                    <div class="my-2 border-t border-gray-200 dark:border-gray-700"></div>
                    <div class="flex items-center justify-between"><span>Total</span><strong class="text-blue-600">Rp {{ number_format($this->total,0,',','.') }}</strong></div>
                </div>
                <div class="mt-3 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-3 text-sm">
                    <div class="font-semibold text-gray-800 dark:text-gray-100 mb-1">Instruksi Pembayaran (Transfer Bank)</div>
                    <ul class="list-disc ml-5 text-gray-700 dark:text-gray-300">
                        <li>Total yang harus dibayar: <strong>Rp {{ number_format($this->total,0,',','.') }}</strong></li>
                        <li>Nama Bank: <strong>{{ config('payment.bank.name') }}</strong></li>
                        <li>No. Rekening: <strong>{{ implode(' ', str_split(config('payment.bank.account'), 4)) }}</strong></li>
                        <li>Atas Nama: <strong>{{ config('payment.bank.holder') }}</strong></li>
                        <li>Kode Referensi: <strong>INV-{{ $this->createdBillId ?? 'XXXX' }}</strong></li>
                    </ul>
                    <p class="mt-2 text-xs text-gray-500">{{ config('payment.bank.note') }} Setelah transfer, silakan unggah bukti pembayaran pada langkah berikutnya.</p>
                </div>
                @if(config('payment.qris.enabled') && config('payment.qris.image_url'))
                    <div class="mt-3 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-3 text-sm">
                        <div class="font-semibold text-gray-800 dark:text-gray-100 mb-1">Pembayaran via QRIS (Opsional)</div>
                        <div class="text-xs text-gray-500 mb-2">{{ config('payment.qris.note') }}</div>
                        <img src="{{ config('payment.qris.image_url') }}" alt="QRIS" class="w-40 h-40 object-contain border rounded-md">
                    </div>
                @endif
                @if(!$this->createdBillId)
                    <div class="mt-3 flex items-center gap-2">
                        <input id="agree" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" required>
                        <label for="agree" class="text-sm text-gray-700 dark:text-gray-300">Saya setuju dengan kebijakan pembatalan.</label>
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        <button class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800" wire:click="back">Kembali</button>
                        <div class="text-right">
                            <button class="inline-flex items-center px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed" @disabled(!$canConfirm)
                                    onclick="if(!document.getElementById('agree').checked){event.preventDefault();alert('Harap setujui kebijakan.');}else{Livewire.find('{{ $this->getId() }}').call('confirm')}">
                                Konfirmasi Pemesanan
                            </button>
                            @unless($canConfirm)
                                <div class="text-xs text-gray-500 mt-1">Pastikan tanggal valid dan minimal satu kamar dipilih.</div>
                            @endunless
                        </div>
                    </div>
                @else
                    <div class="rounded-md bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 text-sm px-3 py-2">Reservasi berhasil dibuat. Silakan unggah bukti pembayaran untuk verifikasi.</div>
                    <div class="mt-3 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unggah Bukti Pembayaran (JPG/PNG/PDF, maks 4MB)</label>
                        <input type="file" wire:model="proofFile" accept=".jpg,.jpeg,.png,.pdf"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500" />
                        @error('proofFile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="proofFile" class="mt-2 text-sm text-gray-500">Mengunggah...</div>
                        <div class="mt-3 flex items-center gap-2">
                            <button class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50" wire:click="uploadProof" wire:loading.attr="disabled" wire:target="proofFile,uploadProof">
                                Kirim Bukti Pembayaran
                            </button>
                            <a href="{{ route('user.bills') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">Lihat Tagihan Saya</a>
                        </div>
                    </div>
                @endif
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
