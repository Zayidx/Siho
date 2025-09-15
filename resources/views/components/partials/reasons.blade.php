<!-- Reasons Section -->
<section id="alasan" class="py-20 fade-in-section">
    <div class="container px-4 mx-auto">
        <div class="mb-12 text-center">
            <h2 class="text-4xl font-bold text-gray-900 dark:text-white">Mengapa Memilih Kami?</h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Keunggulan yang kami tawarkan untuk kenyamanan Anda.
            </p>
        </div>
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @php
                $features = [
                    [
                        'icon' => 'bi-geo-alt-fill',
                        'title' => 'Lokasi Premium',
                        'text' => 'Dekat pusat bisnis, hiburan, dan transportasi.',
                    ],
                    [
                        'icon' => 'bi-star-fill',
                        'title' => 'Kenyamanan Maksimal',
                        'text' => 'Kamar luas, kasur premium, fasilitas modern.',
                    ],
                    [
                        'icon' => 'bi-headset',
                        'title' => 'Layanan 24/7',
                        'text' => 'Tim kami siap membantu kapan pun Anda butuhkan.',
                    ],
                    [
                        'icon' => 'bi-shield-check',
                        'title' => 'Keamanan Terjamin',
                        'text' => 'CCTV, akses kartu, dan petugas profesional.',
                    ],
                    [
                        'icon' => 'bi-wifi',
                        'title' => 'Wi‑Fi Kencang',
                        'text' => 'Koneksi internet stabil di seluruh area hotel.',
                    ],
                    [
                        'icon' => 'bi-cup-hot',
                        'title' => 'Kuliner Istimewa',
                        'text' => 'Menu variatif dari chef berpengalaman.',
                    ],
                ];
            @endphp
            @foreach ($features as $feature)
                <div
                    class="p-8 text-center transition duration-300 transform bg-white rounded-lg shadow-md dark:bg-gray-800 hover:shadow-xl hover:-translate-y-2">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 mb-4 text-blue-600 bg-blue-100 rounded-full dark:bg-gray-700">
                        <i class="text-3xl bi {{ $feature['icon'] }}"></i>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">{{ $feature['title'] }}</h3>
                    <p class="text-gray-600 dark:text-gray-400">{{ $feature['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
