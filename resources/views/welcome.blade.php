<x-layouts.public>

    {{-- 
        Catatan: Layout 'x-layouts.public' diasumsikan sudah memuat:
        1. Tailwind CSS
        2. Google Fonts (Playfair Display & Roboto)
        Layout ini juga harus memiliki mekanisme untuk menambahkan kelas 'dark' pada tag <html> untuk dark mode.
    --}}

    @push('styles')
    {{-- Font Styling --}}
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
        }
    </style>
    @endpush

    <!-- Hero Section -->
    <header class="relative text-center text-white bg-center bg-cover" style="background-image: linear-gradient(rgba(13, 37, 63, 0.6), rgba(13, 37, 63, 0.6)), url('https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1350&q=80');">
        <div class="container relative z-10 px-4 py-32 mx-auto">
            <h1 class="mb-3 text-5xl font-bold md:text-6xl" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">Selamat Datang di {{ config('app.name', 'Grand Luxe') }}</h1>
            <p class="max-w-xl mx-auto mb-6 text-lg leading-relaxed">Nikmati pengalaman menginap tak terlupakan dengan layanan bintang lima dan kemewahan tiada tara.</p>
            <div class="flex flex-col items-center justify-center gap-3 mb-6 sm:flex-row">
                <a href="{{ route('booking.wizard', request()->only(['checkin','checkout'])) }}" class="inline-flex items-center justify-center w-full px-6 py-3 font-semibold text-white transition duration-300 ease-in-out bg-blue-600 border border-transparent rounded-md shadow-sm sm:w-auto hover:bg-blue-700">
                    <i class="mr-2 bi bi-calendar2-check"></i>Pesan Sekarang
                </a>
                <a href="#fasilitas" class="inline-flex items-center justify-center w-full px-6 py-3 font-semibold text-white transition duration-300 ease-in-out bg-transparent border border-white rounded-md shadow-sm sm:w-auto hover:bg-white hover:text-gray-900">
                    <i class="mr-2 bi bi-gem"></i>Lihat Fasilitas
                </a>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <span class="inline-flex items-center px-3 py-1 text-sm text-white bg-white rounded-full bg-opacity-20 backdrop-blur-sm"><i class="mr-1 bi bi-wifi"></i> Wi‑Fi Gratis</span>
                <span class="inline-flex items-center px-3 py-1 text-sm text-white bg-white rounded-full bg-opacity-20 backdrop-blur-sm"><i class="mr-1 bi bi-shield-check"></i> Bebas Biaya Batal*</span>
                <span class="inline-flex items-center px-3 py-1 text-sm text-white bg-white rounded-full bg-opacity-20 backdrop-blur-sm"><i class="mr-1 bi bi-clock"></i> Check‑in 24/7</span>
            </div>
        </div>
    </header>

    <!-- Stats Band -->
    <section class="relative z-20 -mt-12">
        <div class="container px-4 mx-auto">
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="flex items-center p-6 space-x-4 transition duration-300 transform bg-white rounded-lg shadow-lg dark:bg-gray-800 hover:-translate-y-2">
                    <i class="text-4xl text-blue-600 bi bi-emoji-smile"></i>
                    <div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['guestCount'] ?? 0) }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Reservasi</div>
                    </div>
                </div>
                <div class="flex items-center p-6 space-x-4 transition duration-300 transform bg-white rounded-lg shadow-lg dark:bg-gray-800 hover:-translate-y-2">
                    <i class="text-4xl text-blue-600 bi bi-star-fill"></i>
                    <div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">4.8/5</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Ulasan Rata-rata</div>
                    </div>
                </div>
                <div class="flex items-center p-6 space-x-4 transition duration-300 transform bg-white rounded-lg shadow-lg dark:bg-gray-800 hover:-translate-y-2">
                    <i class="text-4xl text-blue-600 bi bi-buildings"></i>
                    <div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['roomCount'] ?? 0) }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Kamar</div>
                    </div>
                </div>
                <div class="flex items-center p-6 space-x-4 transition duration-300 transform bg-white rounded-lg shadow-lg dark:bg-gray-800 hover:-translate-y-2">
                    <i class="text-4xl text-blue-600 bi bi-award"></i>
                    <div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">Top 10</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Hotel di Jakarta</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <main class="text-gray-800 bg-gray-50 dark:bg-gray-900 dark:text-gray-200">
        <!-- Reasons Section -->
        <section id="alasan" class="py-20 fade-in-section">
            <div class="container px-4 mx-auto">
                <div class="mb-12 text-center">
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white">Mengapa Memilih Kami?</h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Keunggulan yang kami tawarkan untuk kenyamanan Anda.</p>
                </div>
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @php
                        $features = [
                            ['icon' => 'bi-geo-alt-fill', 'title' => 'Lokasi Premium', 'text' => 'Dekat pusat bisnis, hiburan, dan transportasi.'],
                            ['icon' => 'bi-star-fill', 'title' => 'Kenyamanan Maksimal', 'text' => 'Kamar luas, kasur premium, fasilitas modern.'],
                            ['icon' => 'bi-headset', 'title' => 'Layanan 24/7', 'text' => 'Tim kami siap membantu kapan pun Anda butuhkan.'],
                            ['icon' => 'bi-shield-check', 'title' => 'Keamanan Terjamin', 'text' => 'CCTV, akses kartu, dan petugas profesional.'],
                            ['icon' => 'bi-wifi', 'title' => 'Wi‑Fi Kencang', 'text' => 'Koneksi internet stabil di seluruh area hotel.'],
                            ['icon' => 'bi-cup-hot', 'title' => 'Kuliner Istimewa', 'text' => 'Menu variatif dari chef berpengalaman.'],
                        ];
                    @endphp
                    @foreach ($features as $feature)
                    <div class="p-8 text-center transition duration-300 transform bg-white rounded-lg shadow-md dark:bg-gray-800 hover:shadow-xl hover:-translate-y-2">
                        <div class="inline-flex items-center justify-center w-16 h-16 mb-4 text-blue-600 bg-blue-100 rounded-full dark:bg-gray-700">
                            <i class="text-3xl bi {{ $feature['icon'] }}"></i>
                        </div>
                        <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">{{ $feature['title'] }}</h3>
                        <p class="text-gray-600 dark:text-gray-400">{{ $feature['text'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Facilities Section -->
        <section id="fasilitas" class="py-20 bg-gray-100 dark:bg-gray-900 fade-in-section">
            <div class="container px-4 mx-auto">
                <div class="mb-12 text-center">
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white">Fasilitas Unggulan</h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Lengkapi pengalaman menginap Anda dengan fasilitas terbaik.</p>
                </div>
                <div class="grid items-center gap-8 lg:grid-cols-2">
                    <div class="relative w-full h-full overflow-hidden rounded-lg shadow-lg">
                        <div id="facilityCarousel" class="relative w-full h-full">
                            <!-- Carousel items -->
                            <div class="duration-700 ease-in-out">
                                <img src="https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?auto=format&fit=crop&w=1200&q=80" class="absolute block w-full h-full object-cover" alt="Kolam renang outdoor Grand Luxe Hotel" onerror="this.onerror=null;this.src='https://placehold.co/1200x800/777/FFF?text=Kolam+Renang';">
                            </div>
                            <div class="hidden duration-700 ease-in-out">
                                <img src="https://images.unsplash.com/photo-1540496905036-5937c10647cc?auto=format&fit=crop&w=1200&q=80" class="absolute block w-full h-full object-cover" alt="Pusat kebugaran dengan peralatan modern" onerror="this.onerror=null;this.src='https://placehold.co/1200x800/777/FFF?text=Gym';">
                            </div>
                            <div class="hidden duration-700 ease-in-out">
                                <img src="https://images.unsplash.com/photo-1512496015851-a90137ba0a43?auto=format&fit=crop&w=1200&q=80" class="absolute block w-full h-full object-cover" alt="Restoran fine dining di Grand Luxe Hotel" onerror="this.onerror=null;this.src='https://placehold.co/1200x800/777/FFF?text=Restoran';">
                            </div>
                        </div>
                    </div>
                    <div>
                        <ul class="space-y-6">
                            @if(($facilities ?? collect())->count())
                                @foreach(($facilities ?? collect()) as $facility)
                                    <li class="flex items-start space-x-4">
                                        <i class="text-3xl text-blue-600 bi {{ $facility->icon ?: 'bi-gear' }}"></i>
                                        <div>
                                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $facility->name }}</h3>
                                            <p class="text-gray-600 dark:text-gray-400">Tersedia untuk meningkatkan kenyamanan Anda.</p>
                                        </div>
                                    </li>
                                @endforeach
                            @else
                                <li class="flex items-start space-x-4"><i class="text-3xl text-blue-600 bi bi-water"></i><div><h3 class="font-semibold text-gray-900 dark:text-white">Kolam Renang Outdoor</h3><p class="text-gray-600 dark:text-gray-400">Tersedia pool bar dan handuk gratis untuk relaksasi maksimal.</p></div></li>
                                <li class="flex items-start space-x-4"><i class="text-3xl text-blue-600 bi bi-bicycle"></i><div><h3 class="font-semibold text-gray-900 dark:text-white">Pusat Kebugaran</h3><p class="text-gray-600 dark:text-gray-400">Peralatan modern dan lengkap, buka 24 jam untuk tamu hotel.</p></div></li>
                                <li class="flex items-start space-x-4"><i class="text-3xl text-blue-600 bi bi-cup-straw"></i><div><h3 class="font-semibold text-gray-900 dark:text-white">Restoran & Bar</h3><p class="text-gray-600 dark:text-gray-400">Sajian menu internasional dan lokal dari chef berpengalaman.</p></div></li>
                                <li class="flex items-start space-x-4"><i class="text-3xl text-blue-600 bi bi-heart-pulse"></i><div><h3 class="font-semibold text-gray-900 dark:text-white">Spa & Wellness</h3><p class="text-gray-600 dark:text-gray-400">Layanan pijat aromaterapi, sauna, dan perawatan tubuh lainnya.</p></div></li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="harga" class="py-20 fade-in-section">
            <div class="container px-4 mx-auto">
                <div class="mb-12 text-center">
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white">Tipe Kamar & Harga</h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Pilih akomodasi yang paling sesuai dengan kebutuhan Anda.</p>
                </div>
                <div class="grid max-w-5xl gap-8 mx-auto md:grid-cols-2 lg:grid-cols-3">
                    @if(($roomTypeSummaries ?? collect())->count() > 0)
                        @foreach(($roomTypeSummaries ?? collect())->take(3) as $idx => $type)
                        <div class="{{ $idx === 1 ? 'relative border-2 border-blue-600 shadow-lg' : 'border border-gray-200 dark:border-gray-700' }} flex flex-col text-center bg-white rounded-lg shadow-sm dark:bg-gray-800">
                            @if($idx === 1)
                                <div class="absolute top-0 px-3 py-1 text-sm font-semibold text-white -translate-x-1/2 bg-blue-600 rounded-full left-1/2 -translate-y-1/2">Paling Populer</div>
                            @endif
                            <div class="p-6 {{ $idx === 1 ? 'pt-10' : '' }}">
                                <h3 class="text-sm font-semibold tracking-widest text-gray-500 uppercase dark:text-gray-400">{{ $type['name'] }}</h3>
                            </div>
                            <div class="px-6 pb-8">
                                <div class="mb-4">
                                    <span class="text-5xl font-bold text-gray-900 dark:text-white">Rp{{ number_format($type['avg_price'] / 1000, 0) }}K</span>
                                    <span class="text-gray-500 dark:text-gray-400">/malam</span>
                                </div>
                                <ul class="mb-6 space-y-2 text-gray-600 dark:text-gray-400">
                                    <li class="flex items-center justify-center"><i class="mr-2 text-green-500 bi bi-check-circle"></i>Tersedia {{ $type['available'] }} kamar</li>
                                    <li class="flex items-center justify-center"><i class="mr-2 text-green-500 bi bi-check-circle"></i>Fasilitas utama tersedia</li>
                                </ul>
                            </div>
                            <div class="p-6 mt-auto bg-gray-50 dark:bg-gray-700/50 rounded-b-lg">
                                <a href="{{ route('booking.wizard', array_merge(['type_id' => $type['id']], request()->only(['checkin','checkout']))) }}" class="block w-full px-4 py-2 font-semibold {{ $idx === 1 ? 'text-white bg-blue-600 border-blue-600 hover:bg-blue-700' : 'text-blue-600 border border-blue-600 hover:bg-blue-600 hover:text-white' }} rounded-md transition duration-300">Pesan {{ $type['name'] }}</a>
                            </div>
                        </div>
                        @endforeach
                    @else
                        {{-- Fallback static cards when no data --}}
                        <div class="flex flex-col text-center bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700"><div class="p-6"><h3 class="text-sm font-semibold tracking-widest text-gray-500 uppercase dark:text-gray-400">Standard Room</h3></div><div class="px-6 pb-8"><div class="mb-4"><span class="text-5xl font-bold text-gray-900 dark:text-white">Rp650K</span><span class="text-gray-500 dark:text-gray-400">/malam</span></div><ul class="mb-6 space-y-2 text-gray-600 dark:text-gray-400"><li class="flex items-center justify-center"><i class="mr-2 text-green-500 bi bi-check-circle"></i>Kamar 28 m²</li><li class="flex items-center justify-center"><i class="mr-2 text-green-500 bi bi-check-circle"></i>Sarapan untuk 2 orang</li><li class="flex items-center justify-center"><i class="mr-2 text-green-500 bi bi-check-circle"></i>Akses kolam & gym</li></ul></div><div class="p-6 mt-auto bg-gray-50 dark:bg-gray-700/50 rounded-b-lg"><a href="{{ route('booking.wizard', request()->only(['checkin','checkout'])) }}" class="block w-full px-4 py-2 font-semibold text-blue-600 transition duration-300 border border-blue-600 rounded-md hover:bg-blue-600 hover:text-white">Pilih Paket</a></div></div>
                        <div class="relative flex flex-col text-center bg-white border-2 border-blue-600 rounded-lg shadow-lg dark:bg-gray-800"><div class="absolute top-0 px-3 py-1 text-sm font-semibold text-white -translate-x-1/2 bg-blue-600 rounded-full left-1/2 -translate-y-1/2">Paling Populer</div><div class="p-6 pt-10"><h3 class="text-sm font-semibold tracking-widest text-gray-500 uppercase dark:text-gray-400">Deluxe Room</h3></div><div class="px-6 pb-8"><div class="mb-4"><span class="text-5xl font-bold text-gray-900 dark:text-white">Rp950K</span><span class="text-gray-500 dark:text-gray-400">/malam</span></div><ul class="mb-6 space-y-2 text-gray-600 dark:text-gray-400"><li class="flex items-center justify-center"><i class="mr-2 text-green-500 bi bi-check-circle"></i>Kamar 32 m²</li><li class="flex items-center justify-center"><i class="mr-2 text-green-500 bi bi-check-circle"></i>Sarapan untuk 2 orang</li><li class="flex items-center justify-center"><i class="mr-2 text-green-500 bi bi-check-circle"></i>Pemandangan kota</li></ul></div><div class="p-6 mt-auto bg-gray-50 dark:bg-gray-700/50 rounded-b-lg"><a href="{{ route('booking.wizard', request()->only(['checkin','checkout'])) }}" class="block w-full px-4 py-2 font-semibold text-white transition duration-300 bg-blue-600 border border-blue-600 rounded-md hover:bg-blue-700">Pesan Deluxe</a></div></div>
                        <div class="flex flex-col text-center bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700"><div class="p-6"><h3 class="text-sm font-semibold tracking-widest text-gray-500 uppercase dark:text-gray-400">Suite Room</h3></div><div class="px-6 pb-8"><div class="mb-4"><span class="text-5xl font-bold text-gray-900 dark:text-white">Rp1.600K</span><span class="text-gray-500 dark:text-gray-400">/malam</span></div><ul class="mb-6 space-y-2 text-gray-600 dark:text-gray-400"><li class="flex items-center justify-center"><i class="mr-2 text-green-500 bi bi-check-circle"></i>Kamar 50 m² + Ruang Tamu</li><li class="flex items-center justify-center"><i class="mr-2 text-green-500 bi bi-check-circle"></i>Akses lounge eksekutif</li><li class="flex items-center justify-center"><i class="mr-2 text-green-500 bi bi-check-circle"></i>Layanan butler 24/7</li></ul></div><div class="p-6 mt-auto bg-gray-50 dark:bg-gray-700/50 rounded-b-lg"><a href="{{ route('booking.wizard', request()->only(['checkin','checkout'])) }}" class="block w-full px-4 py-2 font-semibold text-blue-600 transition duration-300 border border-blue-600 rounded-md hover:bg-blue-600 hover:text-white">Pesan Suite</a></div></div>
                    @endif
                </div>
            </div>
        </section>
        
        <!-- CTA Band -->
        <section class="py-20 fade-in-section">
            <div class="container px-4 mx-auto">
                <div class="p-8 text-white rounded-lg shadow-lg md:p-12" style="background: linear-gradient(95deg, #4682B4, #0d253f);">
                    <div class="flex flex-col items-center justify-between gap-6 lg:flex-row">
                        <div class="text-center lg:text-left">
                            <h3 class="text-3xl font-bold">Siap Menginap di {{ config('app.name', 'Grand Luxe') }}?</h3>
                            <p class="mt-2 opacity-80">Amankan kamar terbaik hari ini. Gratis pembatalan untuk paket terpilih.</p>
                        </div>
                        <a href="{{ route('booking.wizard', request()->only(['checkin','checkout'])) }}" class="flex-shrink-0 px-8 py-3 font-semibold text-blue-600 transition duration-300 bg-white rounded-md shadow-md hover:bg-gray-200">
                            <i class="mr-2 bi bi-calendar2-check"></i>Pesan Sekarang
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section id="testimoni" class="py-20 bg-gray-100 dark:bg-gray-900 fade-in-section">
            <div class="container px-4 mx-auto">
                <div class="mb-12 text-center">
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white">Apa Kata Tamu Kami</h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Pengalaman nyata dari mereka yang telah menginap bersama kami.</p>
                </div>
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <div class="flex flex-col p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                        <div class="flex items-center mb-4">
                            <img src="https://i.pravatar.cc/64?img=5" alt="Foto profil tamu Nadia P." class="w-14 h-14 mr-4 rounded-full" onerror="this.onerror=null;this.src='https://placehold.co/56x56/777/FFF?text=N';">
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white">Nadia P.</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Staycation | Deluxe</div>
                            </div>
                        </div>
                        <p class="mb-4 text-gray-600 dark:text-gray-400">“Kamarnya luas, bersih, dan stafnya ramah sekali. Sarapan enak dan variatif. Pasti kembali lagi!”</p>
                        <div class="mt-auto">
                            <div class="flex items-center text-yellow-500">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">Juli 2024</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                        <div class="flex items-center mb-4">
                            <img src="https://i.pravatar.cc/64?img=12" alt="Foto profil tamu Rizky A." class="w-14 h-14 mr-4 rounded-full" onerror="this.onerror=null;this.src='https://placehold.co/56x56/777/FFF?text=R';">
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white">Rizky A.</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Bisnis | Standard</div>
                            </div>
                        </div>
                        <p class="mb-4 text-gray-600 dark:text-gray-400">“Lokasi strategis, Wi‑Fi kencang, meeting lancar. Sangat direkomendasikan untuk perjalanan bisnis.”</p>
                        <div class="mt-auto">
                            <div class="flex items-center text-yellow-500">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i>
                                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">Mei 2024</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                        <div class="flex items-center mb-4">
                            <img src="https://i.pravatar.cc/64?img=32" alt="Foto profil tamu Michael T." class="w-14 h-14 mr-4 rounded-full" onerror="this.onerror=null;this.src='https://placehold.co/56x56/777/FFF?text=M';">
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white">Michael T.</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Liburan | Suite</div>
                            </div>
                        </div>
                        <p class="mb-4 text-gray-600 dark:text-gray-400">“Pengalaman mewah dengan layanan profesional. Lounge eksekutifnya top! Sangat memanjakan.”</p>
                        <div class="mt-auto">
                            <div class="flex items-center text-yellow-500">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">Maret 2024</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Gallery Section -->
        <section id="galeri" class="py-20 fade-in-section">
            <div class="container px-4 mx-auto">
                <div class="mb-12 text-center">
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white">Galeri Kami</h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Intip kemewahan dan kenyamanan yang menanti Anda.</p>
                </div>
                <div id="hotelGallery" class="relative overflow-hidden rounded-lg shadow-xl">
                    <!-- Slides -->
                    <div class="relative w-full overflow-hidden aspect-video">
                        @php
                            $galleryFallbacks = [
                                'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=1200&q=80',
                                'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80',
                                'https://images.unsplash.com/photo-1590422828236-5627550a7c43?auto=format&fit=crop&w=1200&q=80',
                            ];
                            $images = ($galleryImages ?? collect());
                        @endphp
                        @foreach([0,1,2] as $i)
                            <div class="{{ $i === 0 ? '' : 'hidden' }} gallery-item">
                                <img src="{{ $images[$i] ?? $galleryFallbacks[$i] }}" class="absolute block w-full h-full object-cover" alt="Galeri hotel {{ $i+1 }}" onerror="this.onerror=null;this.src='https://placehold.co/1200x675/777/FFF?text=Galeri';">
                            </div>
                        @endforeach
                    </div>
                    <!-- Controls -->
                    <button type="button" class="gallery-prev absolute top-0 left-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none">
                        <span class="inline-flex items-center justify-center w-10 h-10 bg-white/30 dark:bg-gray-800/30 group-hover:bg-white/50 dark:group-hover:bg-gray-800/60 group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none rounded-full">
                            <i class="text-white bi bi-chevron-left"></i>
                        </span>
                    </button>
                    <button type="button" class="gallery-next absolute top-0 right-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none">
                        <span class="inline-flex items-center justify-center w-10 h-10 bg-white/30 dark:bg-gray-800/30 group-hover:bg-white/50 dark:group-hover:bg-gray-800/60 group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none rounded-full">
                             <i class="text-white bi bi-chevron-right"></i>
                        </span>
                    </button>
                </div>
                <!-- Thumbnails -->
                <div class="flex justify-center p-3 mt-2 space-x-3 bg-gray-100 rounded-b-lg dark:bg-gray-800 carousel-thumbs">
                    @foreach([0,1,2] as $i)
                        <img src="{{ ($galleryImages[$i] ?? null) ? ($galleryImages[$i]) : ($galleryFallbacks[$i] ?? null) }}" class="gallery-thumb block object-cover w-24 h-16 rounded-md cursor-pointer opacity-60" alt="Thumbnail {{ $i+1 }}" onerror="this.onerror=null;this.src='https://placehold.co/100x64/777/FFF?text={{ $i+1 }}';">
                    @endforeach
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section id="faq" class="py-20 bg-gray-100 dark:bg-gray-900 fade-in-section">
            <div class="container px-4 mx-auto">
                <div class="mb-12 text-center">
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white">Pertanyaan Umum</h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Jawaban atas pertanyaan yang sering diajukan oleh tamu kami.</p>
                </div>
                <div class="max-w-3xl mx-auto">
                    <div class="space-y-4" id="faqAccordion">
                        @php
                            $faqs = [
                                ['q' => 'Pukul berapa waktu check-in dan check-out?', 'a' => 'Waktu check-in dimulai pukul 14.00 dan check-out hingga pukul 12.00 siang. Permintaan early check-in atau late check-out bergantung pada ketersediaan kamar.'],
                                ['q' => 'Apakah tersedia Wi‑Fi gratis di seluruh area hotel?', 'a' => 'Ya, kami menyediakan akses Wi‑Fi berkecepatan tinggi secara gratis di seluruh area hotel, termasuk kamar, lobi, restoran, dan fasilitas umum lainnya.'],
                                ['q' => 'Metode pembayaran apa saja yang didukung?', 'a' => 'Kami menerima pembayaran melalui kartu kredit/debit (Visa, MasterCard), transfer bank, dan dompet digital populer. Silakan hubungi resepsionis untuk detail lebih lanjut.'],
                                ['q' => 'Apakah ada kebijakan pembatalan pemesanan?', 'a' => 'Kebijakan pembatalan bervariasi tergantung pada tipe kamar dan promo yang Anda pilih. Rincian lengkap mengenai kebijakan pembatalan akan ditampilkan saat Anda melakukan proses pemesanan.'],
                            ];
                        @endphp
                        @foreach ($faqs as $faq)
                        <div class="bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
                            <button type="button" class="flex items-center justify-between w-full p-5 font-medium text-left text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 faq-toggle">
                                <span>{{ $faq['q'] }}</span>
                                <i class="bi bi-chevron-down transition-transform duration-300"></i>
                            </button>
                            <div class="hidden p-5 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-gray-600 dark:text-gray-400">{{ $faq['a'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="kontak" class="py-20 fade-in-section">
            <div class="container px-4 mx-auto">
                <div class="mb-12 text-center">
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white">Hubungi Kami</h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Kami siap membantu menjawab pertanyaan atau permintaan khusus Anda.</p>
                </div>
                
                <div class="max-w-5xl mx-auto overflow-hidden bg-white rounded-lg shadow-xl dark:bg-gray-800 md:grid md:grid-cols-2">
                    <!-- Contact Info Side -->
                    <div class="p-8 text-white bg-gray-900 md:p-12 dark:bg-gray-900/50" style="background: linear-gradient(135deg, #111827, #1f2937);">
                        <h3 class="text-3xl font-bold">Informasi Kontak</h3>
                        <p class="mt-2 text-gray-300">Hubungi kami melalui detail di bawah ini atau isi formulir di samping.</p>
                        <div class="mt-8 space-y-6">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="p-3 text-2xl text-white bg-white rounded-full bi bi-pin-map-fill bg-opacity-10"></i>
                                </div>
                                <div class="ml-4">
                                    <h4 class="font-semibold">Alamat</h4>
                                    <p class="text-gray-300">Jl. Kemewahan No. 1, Jakarta Pusat, Indonesia</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                     <i class="p-3 text-2xl text-white bg-white rounded-full bi bi-telephone-fill bg-opacity-10"></i>
                                </div>
                                <div class="ml-4">
                                    <h4 class="font-semibold">Telepon</h4>
                                    <p class="text-gray-300">(021) 1234-5678</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                     <i class="p-3 text-2xl text-white bg-white rounded-full bi bi-envelope-fill bg-opacity-10"></i>
                                </div>
                                <div class="ml-4">
                                    <h4 class="font-semibold">Email</h4>
                                    <p class="text-gray-300">{{ $contactEmail ?? 'info@example.com' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Side -->
                    <div class="p-8 md:p-12">
                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white">Kirim Pesan</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Kami akan segera merespons Anda.</p>
                        <div class="mt-8">
                            @livewire('public.contact-form')
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="text-white bg-gray-900">
        <div class="container px-4 py-12 mx-auto">
            <div class="grid gap-8 text-center md:grid-cols-2 lg:grid-cols-4 lg:text-left">
                <div class="mb-6 md:mb-0">
                    <h5 class="mb-4 text-xl font-bold text-blue-500 uppercase">Grand Luxe</h5>
                    <p class="text-gray-400">Pengalaman menginap mewah yang mendefinisikan kembali arti kenyamanan dan keanggunan.</p>
                </div>
                <div class="mb-6 md:mb-0">
                    <h5 class="mb-4 font-bold uppercase">Navigasi</h5>
                    <ul class="space-y-2">
                        <li><a href="#alasan" class="text-gray-400 hover:text-white">Tentang</a></li>
                        <li><a href="#fasilitas" class="text-gray-400 hover:text-white">Fasilitas</a></li>
                        <li><a href="#harga" class="text-gray-400 hover:text-white">Harga</a></li>
                        <li><a href="#galeri" class="text-gray-400 hover:text-white">Galeri</a></li>
                    </ul>
                </div>
                <div class="mb-6 md:mb-0">
                    <h5 class="mb-4 font-bold uppercase">Bantuan</h5>
                    <ul class="space-y-2">
                        <li><a href="#faq" class="text-gray-400 hover:text-white">FAQ</a></li>
                        <li><a href="#kontak" class="text-gray-400 hover:text-white">Kontak</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Kebijakan Privasi</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="mb-4 font-bold uppercase">Kontak</h5>
                    <ul class="space-y-2 text-gray-400">
                        <li class="flex items-center justify-center lg:justify-start"><i class="mr-3 bi bi-house-door-fill"></i>Jl. Kemewahan No. 1, Jakarta</li>
                        <li class="flex items-center justify-center lg:justify-start"><i class="mr-3 bi bi-envelope-fill"></i>{{ $contactEmail ?? 'info@example.com' }}</li>
                        <li class="flex items-center justify-center lg:justify-start"><i class="mr-3 bi bi-telephone-fill"></i>(021) 1234-5678</li>
                    </ul>
                </div>
            </div>
            <hr class="my-8 border-gray-700">
            <div class="flex flex-col items-center justify-between sm:flex-row">
                <p class="text-sm text-gray-400">Hak Cipta ©{{ date('Y') }} <strong>Grand Luxe</strong>. Seluruh hak cipta dilindungi.</p>
                <div class="flex mt-4 space-x-6 sm:justify-center sm:mt-0">
                    <a href="#" class="text-gray-400 hover:text-white" aria-label="Facebook"><i class="text-2xl bi bi-facebook"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white" aria-label="Twitter"><i class="text-2xl bi bi-twitter-x"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white" aria-label="Instagram"><i class="text-2xl bi bi-instagram"></i></a>
                </div>
            </div>
        </div>
    </footer>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Animasi Fade-in
            const sections = document.querySelectorAll('.fade-in-section');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('opacity-100', 'translate-y-0');
                        entry.target.classList.remove('opacity-0', 'translate-y-8');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            sections.forEach(section => {
                section.classList.add('opacity-0', 'translate-y-8', 'transition-all', 'duration-1000', 'ease-out');
                observer.observe(section);
            });

            // Kustom Carousel untuk Fasilitas (Auto-slide)
            const facilityCarousel = document.querySelector('#facilityCarousel');
            if(facilityCarousel) {
                const slides = facilityCarousel.querySelectorAll('div > div');
                let currentSlide = 0;
                const slideInterval = setInterval(() => {
                    slides[currentSlide].classList.add('hidden');
                    currentSlide = (currentSlide + 1) % slides.length;
                    slides[currentSlide].classList.remove('hidden');
                }, 3000);
            }
            
            // Kustom Carousel untuk Galeri (dengan Kontrol)
            const gallery = document.getElementById('hotelGallery');
            if (gallery) {
                const slides = gallery.querySelectorAll('.gallery-item');
                const thumbnails = gallery.parentElement.querySelectorAll('.gallery-thumb');
                const prevButton = gallery.querySelector('.gallery-prev');
                const nextButton = gallery.querySelector('.gallery-next');
                let currentIndex = 0;

                function showSlide(index) {
                    // Sembunyikan slide saat ini
                    slides[currentIndex].classList.add('hidden');
                    // Hapus style aktif dari thumbnail saat ini
                    thumbnails[currentIndex].classList.add('opacity-60');
                    thumbnails[currentIndex].classList.remove('opacity-100', 'border-2', 'border-blue-600');
                    
                    // Tentukan index baru
                    currentIndex = (index + slides.length) % slides.length;
                    
                    // Tampilkan slide baru
                    slides[currentIndex].classList.remove('hidden');
                    // Tambahkan style aktif ke thumbnail baru
                    thumbnails[currentIndex].classList.remove('opacity-60');
                    thumbnails[currentIndex].classList.add('opacity-100', 'border-2', 'border-blue-600');
                }

                prevButton.addEventListener('click', () => showSlide(currentIndex - 1));
                nextButton.addEventListener('click', () => showSlide(currentIndex + 1));
                thumbnails.forEach((thumb, index) => {
                    thumb.addEventListener('click', () => showSlide(index));
                });
                
                // Inisialisasi slide pertama
                showSlide(0);
            }

            // Kustom Accordion untuk FAQ
            const faqToggles = document.querySelectorAll('.faq-toggle');
            faqToggles.forEach(toggle => {
                toggle.addEventListener('click', () => {
                    const content = toggle.nextElementSibling;
                    const icon = toggle.querySelector('i');
                    
                    content.classList.toggle('hidden');
                    icon.classList.toggle('rotate-180');
                });
            });
        });
    </script>

</x-layouts.public>
