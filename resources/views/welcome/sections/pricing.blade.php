<!-- Pricing Section -->
<section id="harga" class="py-20 fade-in-section">
    <div class="container px-4 mx-auto">
        <div class="mb-12 text-center">
            <h2 class="text-4xl font-bold text-gray-900 dark:text-white">Tipe Kamar & Harga</h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Pilih akomodasi yang paling sesuai dengan kebutuhan
                Anda.</p>
        </div>
        <div class="grid max-w-5xl gap-8 mx-auto md:grid-cols-2 lg:grid-cols-3">
            @if (($roomTypeSummaries ?? collect())->count() > 0)
                @foreach (($roomTypeSummaries ?? collect())->take(3) as $idx => $type)
                    <div
                        class="{{ $idx === 1 ? 'relative border-2 border-blue-600 shadow-lg' : 'border border-gray-200 dark:border-gray-700' }} flex flex-col text-center bg-white rounded-lg shadow-sm dark:bg-gray-800">
                        @if ($idx === 1)
                            <div
                                class="absolute top-0 px-3 py-1 text-sm font-semibold text-white -translate-x-1/2 bg-blue-600 rounded-full left-1/2 -translate-y-1/2">
                                Paling Populer</div>
                        @endif
                        <div class="w-full h-36 bg-gray-200 dark:bg-gray-700">
                            <img src="{{ $roomTypeCovers[$type['id']] ?? null ?: 'https://images.unsplash.com/photo-1551776235-dde6d4829808?auto=format&fit=crop&w=1200&q=60' }}"
                                alt="{{ $type['name'] }}" class="w-full h-full object-cover fs-img cursor-zoom-in"
                                loading="lazy" decoding="async"
                                onerror="this.onerror=null;this.src='https://placehold.co/1200x600/777/FFF?text=Kamar';">
                        </div>
                        @php $imgs = ($roomTypeImages[$type['id']] ?? []); @endphp
                        @if (!empty($imgs) && count($imgs) > 1)
                            <div class="px-4 mt-2 flex gap-4 overflow-x-auto">
                                @foreach ($imgs as $ix => $img)
                                    @continue($ix === 0)
                                    <div class="flex flex-col items-center gap-1">
                                        <img src="{{ $img['url'] ?? '' }}" alt="thumb"
                                            title="{{ $type['name'] }} - {{ !empty($img['category']) ? ucfirst($img['category']) : 'Foto' }}"
                                            class="w-14 h-14 rounded object-cover fs-img cursor-zoom-in" loading="lazy"
                                            decoding="async"
                                            onerror="this.onerror=null;this.src='https://placehold.co/160x160/777/FFF?text=Foto';">
                                        <span
                                            class="text-[10px] text-gray-500 dark:text-gray-400">{{ !empty($img['category']) ? ucfirst($img['category']) : 'Foto' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <div class="p-6 {{ $idx === 1 ? 'pt-10' : '' }}">
                            <h3
                                class="text-sm font-semibold tracking-widest text-gray-500 uppercase dark:text-gray-400">
                                {{ $type['name'] }}</h3>
                        </div>
                        <div class="px-6 pb-8">
                            <div class="mb-4">
                                <span
                                    class="text-5xl font-bold text-gray-900 dark:text-white">Rp{{ number_format($type['avg_price'] / 1000, 0) }}K</span>
                                <span class="text-gray-500 dark:text-gray-400">/malam</span>
                            </div>
                            <ul class="mb-6 space-y-2 text-gray-600 dark:text-gray-400">
                                <li class="flex items-center justify-center"><i
                                        class="mr-2 text-green-500 bi bi-check-circle"></i>Tersedia
                                    {{ $type['available'] }} kamar</li>
                                <li class="flex items-center justify-center"><i
                                        class="mr-2 text-green-500 bi bi-check-circle"></i>Fasilitas utama tersedia</li>
                            </ul>
                        </div>
                        <div class="p-6 mt-auto bg-gray-50 dark:bg-gray-700/50 rounded-b-lg">
                            <a href="{{ route('booking.hotel', array_merge(['type_id' => $type['id']], request()->only(['checkin', 'checkout']))) }}"
                                class="block w-full px-4 py-2 font-semibold {{ $idx === 1 ? 'text-white bg-blue-600 border-blue-600 hover:bg-blue-700' : 'text-blue-600 border border-blue-600 hover:bg-blue-600 hover:text-white' }} rounded-md transition duration-300">Pesan
                                {{ $type['name'] }}</a>
                        </div>
                    </div>
                @endforeach
            @else
                <div
                    class="flex flex-col text-center bg-white rounded-lg shadow-sm dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-8">
                    <div class="text-gray-500 dark:text-gray-400">Belum ada data tipe kamar.</div>
                </div>
            @endif
        </div>
    </div>
</section>
