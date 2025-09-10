<!-- Menu Samples Section -->
<section id="menu-samples" class="py-20 bg-gray-50 dark:bg-gray-900 fade-in-section">
    <div class="container px-4 mx-auto">
        <div class="mb-12 text-center">
            <h2 class="text-4xl font-bold text-gray-900 dark:text-white">Cicipi Menu Kami</h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Beberapa pilihan lainnya untuk menggugah selera
                Anda.</p>
        </div>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($menuSamples as $m)
                <div class="relative p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
                    @if ($m->is_popular)
                        <span
                            class="absolute top-2 left-2 text-[11px] px-2 py-0.5 rounded bg-yellow-400 text-gray-900 font-semibold">Populer</span>
                    @endif
                    @if ($m->image)
                        <img src="{{ str_starts_with($m->image, 'http') ? $m->image : asset('storage/' . $m->image) }}"
                            alt="{{ $m->name }}"
                            class="w-full h-40 object-cover rounded mb-3 fs-img cursor-zoom-in" loading="lazy"
                            decoding="async"
                            onerror="this.onerror=null;this.src='https://placehold.co/600x400/777/FFF?text=Menu';">
                    @endif
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $m->name }}</h3>
                            @if ($m->category)
                                <span
                                    class="inline-block mt-1 text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">{{ $m->category->name }}</span>
                            @endif
                            <div class="text-gray-500">Rp{{ number_format($m->price, 0, ',', '.') }}</div>
                        </div>
                        @auth
                            <button
                                class="px-3 py-1.5 text-sm rounded bg-blue-600 text-white hover:bg-blue-700 quick-add-menu"
                                data-item-id="{{ $m->id }}">Tambah</button>
                        @else
                            <a href="{{ route('menu', ['add' => $m->id]) }}"
                                class="px-3 py-1.5 text-sm rounded bg-blue-600 text-white hover:bg-blue-700">Pesan</a>
                        @endauth
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-8 text-center">
            <a href="{{ route('menu') }}"
                class="inline-flex items-center px-5 py-2.5 rounded-md bg-blue-600 text-white font-semibold hover:bg-blue-700">
                <i class="bi bi-egg-fried mr-2"></i> Lihat Semua Menu
            </a>
        </div>
    </div>
</section>
