<div class="container mx-auto px-4 py-10">
    <div wire:loading.delay class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 px-4 py-2 rounded shadow text-sm">Memuat...</div>
    </div>
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <h1 class="text-3xl font-bold">Menu Restoran</h1>
        <div class="flex gap-3 items-center">
            <input type="search" wire:model.live.debounce.300ms="search" class="border rounded px-3 py-2" placeholder="Cari menu...">
            <select wire:model.live="selectedCategory" class="border rounded px-3 py-2">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                @endforeach
            </select>
            <a href="{{ route('home') }}#menu" class="text-blue-600 hover:underline">Lihat Menu Populer</a>
        </div>
    </div>

    <div class="mt-3 flex flex-wrap gap-2">
        <button class="px-3 py-1.5 rounded border text-sm {{ empty($selectedCategory) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700' }}" wire:click="$set('selectedCategory', null)">Semua</button>
        @foreach($categories as $cat)
            <button class="px-3 py-1.5 rounded border text-sm {{ (string)$selectedCategory === (string)$cat['id'] ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700' }}" wire:click="$set('selectedCategory', {{ $cat['id'] }})">{{ $cat['name'] }}</button>
        @endforeach
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
        @forelse($items as $it)
            <div class="relative bg-white dark:bg-gray-800 rounded shadow p-4 flex flex-col">
                @if(!empty($it['is_popular']))
                    <span class="absolute top-2 left-2 text-[11px] px-2 py-0.5 rounded bg-yellow-400 text-gray-900 font-semibold">Populer</span>
                @endif
                @if(!empty($it['image']))
                    <img src="{{ str_starts_with($it['image'], 'http') ? $it['image'] : asset('storage/'.$it['image']) }}" alt="{{ $it['name'] }}" class="w-full h-40 object-cover rounded mb-3">
                @endif
                <div class="font-semibold text-lg text-gray-900 dark:text-white">{{ $it['name'] }}</div>
                @if(!empty($it['category_name']))
                    <span class="inline-block mt-1 text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">{{ $it['category_name'] }}</span>
                @endif
                <div class="text-gray-600 dark:text-gray-400 text-sm flex-1">{{ $it['description'] }}</div>
                <div class="mt-3 flex items-center justify-between">
                    <div class="text-xl font-bold text-gray-900 dark:text-white">Rp{{ number_format($it['price'],0,',','.') }}</div>
                    <button wire:click="addToCart({{ $it['id'] }})" class="px-3 py-1.5 rounded bg-blue-600 text-white hover:bg-blue-700">Tambah</button>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center text-gray-500">Tidak ada menu</div>
        @endforelse
    </div>

    <div class="mt-10 grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 pb-20 md:pb-0">
            <h2 class="text-2xl font-semibold mb-3">Catatan & Kamar</h2>
            <div class="grid md:grid-cols-3 gap-4">
                <select wire:model="serviceType" class="border rounded px-3 py-2">
                    <option value="in_room">Di Kamar</option>
                    <option value="dine_in">Makan di Restoran</option>
                    <option value="takeaway">Bawa Pulang</option>
                </select>
                @if($serviceType === 'in_room')
                    <input type="text" wire:model="roomNumber" class="border rounded px-3 py-2" placeholder="Nomor kamar (wajib untuk Di Kamar)">
                @else
                    <input type="text" class="border rounded px-3 py-2 opacity-50" placeholder="Nomor kamar (khusus Di Kamar)" disabled>
                @endif
                <input type="text" wire:model="note" class="border rounded px-3 py-2" placeholder="Catatan pesanan (opsional)">
            </div>
        </div>
        <div class="relative">
            <h2 class="text-2xl font-semibold mb-3">Keranjang</h2>
            @error('cart')<div class="text-red-600 text-sm mb-2">{{ $message }}</div>@enderror
            <div class="bg-white dark:bg-gray-800 rounded shadow divide-y">
                @forelse($cart as $row)
                    <div class="p-3 flex items-center justify-between">
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</div>
                            <div class="text-gray-500 text-sm">Rp{{ number_format($row['price'],0,',','.') }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="px-2 py-1 border rounded" wire:click="dec({{ $row['id'] }})">-</button>
                            <span class="w-8 text-center">{{ $row['qty'] }}</span>
                            <button class="px-2 py-1 border rounded" wire:click="inc({{ $row['id'] }})">+</button>
                            <button class="px-2 py-1 border rounded text-red-600" onclick="if(!confirm('Hapus item ini dari keranjang?')) return false;" wire:click="remove({{ $row['id'] }})">Hapus</button>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-gray-500">Keranjang kosong</div>
                @endforelse
                <div class="p-4 flex items-center justify-between">
                    <div class="font-semibold">Total</div>
                    <div class="text-xl font-bold">Rp{{ number_format($this->total,0,',','.') }}</div>
                </div>
            </div>
            <button wire:click="checkout" class="w-full mt-3 px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700 disabled:opacity-50" @disabled(empty($cart))>Pesan Sekarang</button>
        </div>
    </div>

    <!-- Sticky mobile cart bar -->
    @if(!empty($cart))
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-3 flex items-center justify-between z-40">
        <div class="font-semibold">Total: Rp{{ number_format($this->total,0,',','.') }}</div>
        <button wire:click="checkout" class="px-4 py-2 rounded bg-green-600 text-white disabled:opacity-50" @disabled(empty($cart))>Checkout</button>
    </div>
    @endif
</div>
