<!-- Facilities Section -->
<section id="fasilitas" class="py-20 bg-gray-100 dark:bg-gray-900 fade-in-section">
    <div class="container px-4 mx-auto">
        <div class="mb-12 text-center">
            <h2 class="text-4xl font-bold text-gray-900 dark:text-white">Fasilitas Unggulan</h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Lengkapi pengalaman menginap Anda dengan fasilitas terbaik.</p>
        </div>
        <div class="grid items-center gap-8 lg:grid-cols-2">
            <div class="relative w-full overflow-hidden rounded-lg shadow-lg h-56 sm:h-72 md:h-96">
                <div id="facilityCarousel" class="relative w-full h-full">
                    <!-- Carousel items -->
                    <div class="duration-700 ease-in-out">
                        <img src="https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?auto=format&fit=crop&w=1200&q=80" class="absolute block w-full h-full object-cover" alt="Kolam renang outdoor Grand Luxe Hotel" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='https://placehold.co/1200x800/777/FFF?text=Kolam+Renang';">
                    </div>
                    <div class="hidden duration-700 ease-in-out">
                        <img src="https://images.unsplash.com/photo-1540496905036-5937c10647cc?auto=format&fit=crop&w=1200&q=80" class="absolute block w-full h-full object-cover" alt="Pusat kebugaran dengan peralatan modern" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='https://placehold.co/1200x800/777/FFF?text=Gym';">
                    </div>
                    <div class="hidden duration-700 ease-in-out">
                        <img src="https://www.saniharto.com/assets/gallery/Gambar_Resotran_Park_Hyatt_Jakarta.jpeg" class="absolute block w-full h-full object-cover" alt="Restoran fine dining di Grand Luxe Hotel" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='https://placehold.co/1200x800/777/FFF?text=Restoran';">
                    </div>
                </div>
            </div>
            <div>
                <ul class="space-y-6">
                    @if(($facilities ?? collect())->count())
                        @foreach(($facilities ?? collect()) as $facility)
                            <li class="flex items-start space-x-4">
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $facility->name }}</h3>
                                    <p class="text-gray-600 dark:text-gray-400">Tersedia untuk meningkatkan kenyamanan Anda.</p>
                                </div>
                            </li>
                        @endforeach
                    @else
                        <li class="flex items-start space-x-4"><i class="text-3xl text-blue-600 bi bi-heart-pulse"></i><div><h3 class="font-semibold text-gray-900 dark:text-white">Spa & Wellness</h3><p class="text-gray-600 dark:text-gray-400">Layanan pijat aromaterapi, sauna, dan perawatan tubuh lainnya.</p></div></li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</section>

