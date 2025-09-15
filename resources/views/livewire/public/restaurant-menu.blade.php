@push('styles')
    <!-- Tailwind is already loaded in the public layout -->
    <style>
        @keyframes cart-slide-in {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
        @keyframes overlay-fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .animate-cart-in { animation: cart-slide-in 0.3s ease-out both; }
        .animate-overlay-in { animation: overlay-fade-in 0.2s ease-out both; }
    </style>
@endpush
<div class="container mx-auto px-4 py-10">
    <div wire:loading.delay class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 px-4 py-2 rounded shadow text-sm">Memuat...</div>
    </div>
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Menu Restoran</h1>
        <div class="flex gap-3 items-center">
            <input type="search" wire:model.live.debounce.300ms="search"
                class="border border-gray-300 dark:border-gray-700 rounded px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Cari menu...">
            <select wire:model.live="selectedCategory"
                class="border border-gray-300 dark:border-gray-700 rounded px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                @endforeach
            </select>
            <a href="{{ route('home') }}#menu" class="text-blue-600 dark:text-blue-400 hover:underline">Lihat Menu
                Populer</a>
        </div>
    </div>

    <div class="mt-3 flex flex-wrap gap-2">
        <button
            class="px-3 py-1.5 rounded border text-sm {{ empty($selectedCategory) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200' }}"
            wire:click="$set('selectedCategory', null)">Semua</button>
        @foreach ($categories as $cat)
            <button
                class="px-3 py-1.5 rounded border text-sm {{ (string) $selectedCategory === (string) $cat['id'] ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200' }}"
                wire:click="$set('selectedCategory', {{ $cat['id'] }})">{{ $cat['name'] }}</button>
        @endforeach
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
        @forelse($items as $it)
            <div class="relative bg-white dark:bg-gray-800 rounded shadow p-4 flex flex-col">
                @if (!empty($it['is_popular']))
                    <span
                        class="absolute top-2 left-2 text-[11px] px-2 py-0.5 rounded bg-yellow-400 text-gray-900 font-semibold">Populer</span>
                @endif
                @if (!empty($it['image']))
                    <img src="{{ str_starts_with($it['image'], 'http') ? $it['image'] : asset('storage/' . $it['image']) }}"
                        alt="{{ $it['name'] }}" class="w-full h-40 object-cover rounded mb-3" loading="lazy"
                        decoding="async"
                        onerror="this.onerror=null;this.src='https://placehold.co/600x400/777/FFF?text=Menu';">
                @endif
                <div class="font-semibold text-lg text-gray-900 dark:text-white">{{ $it['name'] }}</div>
                @if (!empty($it['category_name']))
                    <span
                        class="inline-block mt-1 text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">{{ $it['category_name'] }}</span>
                @endif
                <div class="text-gray-600 dark:text-gray-400 text-sm flex-1">{{ $it['description'] }}</div>
                <div class="mt-3 flex items-center justify-between">
                    <div class="text-xl font-bold text-gray-900 dark:text-white">
                        Rp{{ number_format($it['price'], 0, ',', '.') }}</div>
                    <button wire:click="addToCart({{ $it['id'] }})"
                        class="px-3 py-1.5 rounded bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">Tambah</button>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center text-gray-500 dark:text-gray-400">Tidak ada menu</div>
        @endforelse
    </div>

    <!-- Floating Cart Button (visible only when cart has items) -->
    @if (!empty($cart))
        <button type="button" wire:click="openCart"
            class="fixed bottom-5 right-5 z-40 inline-flex items-center gap-2 px-4 py-2 rounded-full shadow-lg bg-blue-600 text-white hover:bg-blue-700">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25h9.75m-9.75 0L6.106 6.272A1.125 1.125 0 0 0 4.98 5.25H3.75m3.75 9 11.215-1.868a1.125 1.125 0 0 0 .948-.88l.795-3.973A1.125 1.125 0 0 0 19.35 6.75H5.116" />
            </svg>
            <span>Keranjang</span>
            <span class="ml-1 text-xs bg-white/20 rounded-full px-2 py-0.5">{{ $this->cartQty }}</span>
            <span class="ml-2 font-semibold">Rp{{ number_format($this->total, 0, ',', '.') }}</span>
        </button>
    @endif

    <!-- Cart Modal -->
    @if ($showCartModal)
        <div class="fixed inset-0 z-50 flex items-stretch justify-end">
            <div class="absolute inset-0 bg-black/50 animate-overlay-in" onclick="window._cartClose('{{ $this->getId() }}')"></div>
            <!-- Slide-over drawer -->
            <div id="cartDrawer"
                class="relative h-full w-full max-w-md bg-white dark:bg-gray-900 shadow-xl p-4 md:p-6 transition-transform duration-300 translate-x-0 animate-cart-in flex flex-col">
                <div class="shrink-0 flex items-center justify-between mb-3 border-b pb-2">
                    <h2 class="text-xl font-semibold">Keranjang</h2>
                    <button onclick="window._cartClose('{{ $this->getId() }}')"
                        class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">Tutup</button>
                </div>

                @error('cart')
                    <div class="text-red-600 text-sm mb-2">{{ $message }}</div>
                @enderror

                <div class="flex-1 min-h-0 overflow-y-auto pr-1">
                    <div class="divide-y rounded border border-gray-200 dark:border-gray-700 mb-4">
                    @forelse($cart as $row)
                        <div class="p-3 flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</div>
                                <div class="text-gray-500 text-sm">Rp{{ number_format($row['price'], 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    class="px-2 py-1 border rounded border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800"
                                    wire:click="dec({{ $row['id'] }})">-</button>
                                <span class="w-8 text-center">{{ $row['qty'] }}</span>
                                <button
                                    class="px-2 py-1 border rounded border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800"
                                    wire:click="inc({{ $row['id'] }})">+</button>
                                <button
                                    class="px-2 py-1 border rounded border-gray-300 dark:border-gray-700 text-red-600 dark:text-red-400 hover:bg-gray-50 dark:hover:bg-gray-800"
                                    wire:click="remove({{ $row['id'] }})"
                                    wire:confirm="Hapus item ini dari keranjang?">Hapus</button>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-gray-500 dark:text-gray-400">Keranjang kosong</div>
                    @endforelse
                    </div>

                    <div class="grid md:grid-cols-3 gap-3 mb-3">
                    <select wire:model="serviceType"
                        class="border border-gray-300 dark:border-gray-700 rounded px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="in_room">Di Kamar</option>
                        <option value="dine_in">Makan di Restoran</option>
                        <option value="takeaway">Bawa Pulang</option>
                    </select>
                    @if ($serviceType === 'in_room')
                        <input type="text" wire:model="roomNumber"
                            class="border border-gray-300 dark:border-gray-700 rounded px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nomor kamar (wajib untuk Di Kamar)">
                    @else
                        <input type="text"
                            class="border border-gray-300 dark:border-gray-700 rounded px-3 py-2 opacity-50 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                            placeholder="Nomor kamar (khusus Di Kamar)" disabled>
                    @endif
                    <input type="text" wire:model="note"
                        class="border border-gray-300 dark:border-gray-700 rounded px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Catatan pesanan (opsional)">
                    </div>

                    <!-- Payment Instructions -->
                    <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-3 text-sm mb-3">
                    <div class="font-semibold text-gray-800 dark:text-gray-100 mb-1">Instruksi Pembayaran (Transfer Bank)
                    </div>
                    <ul class="list-disc ml-5 text-gray-700 dark:text-gray-300">
                        <li>Total yang harus dibayar: <strong>Rp {{ number_format($this->total, 0, ',', '.') }}</strong>
                        </li>
                        <li>Nama Bank: <strong>{{ config('payment.bank.name') }}</strong></li>
                        <li>No. Rekening: <strong>{{ implode(' ', str_split(config('payment.bank.account'), 4)) }}</strong>
                        </li>
                        <li>Atas Nama: <strong>{{ config('payment.bank.holder') }}</strong></li>
                    </ul>
                    <p class="mt-2 text-xs text-gray-500">{{ config('payment.bank.note') }}</p>
                    </div>
                    @if (config('payment.qris.enabled') && config('payment.qris.image_url'))
                        <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-3 text-sm mb-3">
                        <div class="font-semibold text-gray-800 dark:text-gray-100 mb-1">Pembayaran via QRIS (Opsional)</div>
                        <div class="text-xs text-gray-500 mb-2">{{ config('payment.qris.note') }}</div>
                        <img src="{{ config('payment.qris.image_url') }}" alt="QRIS" class="w-40 h-40 object-contain border rounded-md">
                        </div>
                    @endif

                    <!-- Upload Payment Proof -->
                    <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-3 text-sm mb-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unggah Bukti Pembayaran (JPG/PNG/PDF, maks 4MB)</label>
                    <input type="file" wire:model="proofFile" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500">
                    @error('proofFile')
                        <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                    @enderror
                    <div wire:loading wire:target="proofFile" class="mt-1 text-xs text-gray-500">Mengunggah...</div>
                    </div>
                </div>

                <div class="shrink-0 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-2 bg-inherit">
                    <div class="text-lg font-semibold">Total: Rp{{ number_format($this->total, 0, ',', '.') }}</div>
                    <div class="flex gap-2">
                        <button onclick="window._cartClose('{{ $this->getId() }}')"
                            class="px-4 py-2 rounded border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">Lanjut
                            Belanja</button>
                        <button wire:click="checkout"
                            class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            @disabled(empty($cart) || empty($proofFile))
                            wire:loading.attr="disabled" wire:target="checkout,proofFile">Pesan Sekarang</button>
                    </div>
                </div>
                {{-- Drawer opens already visible; close animation handled in _cartClose() --}}
            </div>
        </div>
    @endif
</div>
@push('scripts')
    <script>
        window._cartClose = function(id) {
            try {
                var el = document.getElementById('cartDrawer');
                if (el) {
                    el.classList.remove('translate-x-0');
                    el.classList.add('translate-x-full');
                    setTimeout(function() {
                        if (window.Livewire && typeof window.Livewire.find === 'function') {
                            window.Livewire.find(id).call('closeCart');
                        }
                    }, 300);
                } else if (window.Livewire && typeof window.Livewire.find === 'function') {
                    window.Livewire.find(id).call('closeCart');
                }
            } catch (e) {
                if (window.Livewire && typeof window.Livewire.find === 'function') {
                    window.Livewire.find(id).call('closeCart');
                }
            }
        };
    </script>
@endpush
