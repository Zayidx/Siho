<!-- Gallery Section -->
<section id="galeri" class="py-20 fade-in-section">
    <div class="container px-4 mx-auto">
        <div class="mb-12 text-center">
            <h2 class="text-4xl font-bold text-gray-900 dark:text-white">Galeri Kami</h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Intip kemewahan dan kenyamanan yang menanti Anda.</p>
        </div>
        <div class="flex justify-end mb-2">
            <a href="{{ route('gallery') }}" class="inline-flex items-center px-4 py-2 text-blue-600 transition bg-blue-100 rounded-md hover:bg-blue-200 dark:bg-gray-800 dark:text-blue-400 dark:hover:bg-gray-700">Lihat Semua Galeri</a>
        </div>
        <div id="hotelGallery" class="relative overflow-hidden rounded-lg shadow-xl">
            <div class="relative w-full overflow-hidden h-48 sm:h-64 md:h-80" style="aspect-ratio: 16 / 9;">
                @php
                    $categories = ['facade' => 'Fasad', 'facilities' => 'Fasilitas', 'public' => 'Public Space', 'restaurant' => 'Restoran', 'room' => 'Kamar'];
                    $images = ($galleryByCategory ?? collect());
                    $fallback = 'https://placehold.co/1200x675/777/FFF?text=Galeri';
                @endphp
                @foreach($categories as $key => $label)
                    <div class="{{ $loop->first ? '' : 'hidden' }} gallery-item">
                        <img src="{{ $images[$key] ?? $fallback }}" class="absolute block object-cover w-full h-full cursor-zoom-in fs-img" alt="{{ $label }}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='{{ $fallback }}';">
                    </div>
                @endforeach
            </div>
            <button type="button" class="absolute top-0 left-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer gallery-prev group focus:outline-none">
                <span class="inline-flex items-center justify-center w-10 h-10 bg-white/30 dark:bg-gray-800/30 group-hover:bg-white/50 dark:group-hover:bg-gray-800/60 group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none rounded-full">
                    <i class="text-white bi bi-chevron-left"></i>
                </span>
            </button>
            <button type="button" class="absolute top-0 right-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer gallery-next group focus:outline-none">
                <span class="inline-flex items-center justify-center w-10 h-10 bg-white/30 dark:bg-gray-800/30 group-hover:bg-white/50 dark:group-hover:bg-gray-800/60 group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none rounded-full">
                    <i class="text-white bi bi-chevron-right"></i>
                </span>
            </button>
        </div>
    </div>
</section>

