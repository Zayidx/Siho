<x-layouts.public>

    {{-- 
        Catatan: Diasumsikan layout 'x-layouts.public' Anda sudah memuat:
        1. Bootstrap 5.3+ CSS & JS
        2. Bootstrap Icons
        3. Google Fonts (Playfair Display & Roboto)
        Jika belum, tambahkan <link> dan <style> di bawah ini ke dalam <head> layout Anda.
    --}}

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bs-primary-rgb: 70, 130, 180; /* SteelBlue for a more elegant look */
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
        }
        .hero-section {
            /* Menggunakan gambar yang Anda berikan */
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://www.kayak.co.id/rimg/himg/2e/7b/a5/expedia_group-94818-faad0b-358361.jpg?width=1366&height=768&crop=true') no-repeat center center;
            background-size: cover;
            color: white;
            padding: 10rem 0;
            text-align: center;
        }
        .icon-feature {
            font-size: 3rem;
            color: var(--bs-primary);
        }
        .card-feature {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 0;
            background-color: #ffffff;
        }
        .card-feature:hover {
            transform: translateY(-10px);
            box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
        }
        .facility-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            background-color: white;
            border-radius: .5rem;
            padding: 2rem;
            transition: transform 0.3s ease, background-color 0.3s ease, color 0.3s ease;
            border: 1px solid #eee;
        }
        .facility-item:hover {
            transform: scale(1.05);
            background-color: #0d6efd; /* Bootstrap primary color */
            color: white;
        }
        .facility-item i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--bs-primary);
            transition: color 0.3s ease;
        }
        .facility-item:hover i {
            color: white;
        }
        .section-bg {
            background-color: #ffffff;
        }
        /* Animasi fade-in saat scroll */
        .fade-in-section {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        .fade-in-section.is-visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
    @endpush

    <!-- Hero Section -->
    <header class="hero-section">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Selamat Datang di Grand Luxe</h1>
            <p class="lead mb-4">Nikmati pengalaman menginap yang tak terlupakan dengan layanan bintang lima dan kemewahan tiada tara.</p>
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <a href="{{ route('booking') }}" class="btn btn-primary btn-lg px-4 gap-3">
                    <i class="bi bi-calendar-check me-2"></i>Pesan Sekarang
                </a>
                <a href="#fasilitas" class="btn btn-outline-light btn-lg px-4">
                    <i class="bi bi-arrow-down-circle me-2"></i>Lihat Fasilitas
                </a>
            </div>
        </div>
    </header>

    <main class="container py-5">
        <!-- Reasons Section -->
        <section id="alasan" class="py-5 text-center fade-in-section">
            <h2 class="fw-bold mb-5">Mengapa Memilih Kami?</h2>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card h-100 shadow-sm card-feature">
                        <div class="card-body p-4">
                            <div class="icon-feature mb-3"><i class="bi bi-geo-alt-fill"></i></div>
                            <h5 class="card-title fw-bold">Lokasi Premium</h5>
                            <p class="card-text text-muted">Akses mudah ke pusat bisnis dan hiburan kota.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100 shadow-sm card-feature">
                        <div class="card-body p-4">
                            <div class="icon-feature mb-3"><i class="bi bi-star-fill"></i></div>
                            <h5 class="card-title fw-bold">Kenyamanan Maksimal</h5>
                            <p class="card-text text-muted">Kamar luas, kasur empuk, dan fasilitas modern.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100 shadow-sm card-feature">
                        <div class="card-body p-4">
                            <div class="icon-feature mb-3"><i class="bi bi-headset"></i></div>
                            <h5 class="card-title fw-bold">Layanan 24/7</h5>
                            <p class="card-text text-muted">Tim kami siap membantu kapan pun Anda butuh.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Facilities Section -->
        <section id="fasilitas" class="py-5 section-bg rounded shadow-sm my-5 fade-in-section">
            <div class="container">
                <h2 class="fw-bold text-center mb-5">Fasilitas Unggulan</h2>
                <div class="row g-4">
                    <div class="col-md-6 col-lg-3"><div class="facility-item h-100"><i class="bi bi-water"></i><span>Kolam Renang</span></div></div>
                    <div class="col-md-6 col-lg-3"><div class="facility-item h-100"><i class="bi bi-bicycle"></i><span>Pusat Kebugaran</span></div></div>
                    <div class="col-md-6 col-lg-3"><div class="facility-item h-100"><i class="bi bi-heart-pulse"></i><span>Spa & Wellness</span></div></div>
                    <div class="col-md-6 col-lg-3"><div class="facility-item h-100"><i class="bi bi-cup-straw"></i><span>Restoran Fine Dining</span></div></div>
                </div>
            </div>
        </section>

        <!-- Gallery Section with Carousel -->
        <section id="galeri" class="py-5 fade-in-section">
            <h2 class="fw-bold text-center mb-5">Galeri Kami</h2>
            <div id="hotelGallery" class="carousel slide shadow-lg rounded overflow-hidden" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#hotelGallery" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#hotelGallery" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#hotelGallery" data-bs-slide-to="2" aria-label="Slide 3"></button>
                </div>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="https://images.unsplash.com/photo-1551776235-dde6d4829808?auto=format&fit=crop&w=1200&q=80" class="d-block w-100" alt="Kamar Hotel" onerror="this.onerror=null;this.src='https://www.kayak.co.id/rimg/himg/2e/7b/a5/expedia_group-94818-faad0b-358361.jpg?width=1366&height=768&crop=true';">
                        <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-2">
                            <h5>Kamar Suite Mewah</h5>
                            <p>Dirancang untuk kenyamanan dan ketenangan Anda.</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=1200&q=80" class="d-block w-100" alt="Lobi Hotel" onerror="this.onerror=null;this.src='https://www.kayak.co.id/rimg/himg/2e/7b/a5/expedia_group-94818-faad0b-358361.jpg?width=1366&height=768&crop=true';">
                         <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-2">
                            <h5>Lobi Elegan</h5>
                            <p>Sambutan hangat menanti Anda saat pertama kali tiba.</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="https://images.unsplash.com/photo-1501117716987-c8e2a3a67c73?auto=format&fit=crop&w=1200&q=80" class="d-block w-100" alt="Restoran Hotel" onerror="this.onerror=null;this.src='https://www.kayak.co.id/rimg/himg/2e/7b/a5/expedia_group-94818-faad0b-358361.jpg?width=1366&height=768&crop=true';">
                         <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-2">
                            <h5>Restoran Kelas Atas</h5>
                            <p>Sajian kuliner istimewa dari chef berpengalaman.</p>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#hotelGallery" data-bs-slide-to="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#hotelGallery" data-bs-slide-to="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="kontak" class="py-5 my-5 fade-in-section">
            <h2 class="fw-bold text-center mb-5">Hubungi Kami</h2>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold mb-3">Informasi Kontak</h5>
                            <ul class="list-unstyled mb-0 text-muted">
                                <li class="mb-3 d-flex align-items-start"><i class="bi bi-pin-map-fill fs-4 me-3 text-primary"></i> <span><strong>Alamat:</strong><br>Jl. Kemewahan No. 1, Jakarta</span></li>
                                <li class="mb-3 d-flex align-items-start"><i class="bi bi-telephone-fill fs-4 me-3 text-primary"></i> <span><strong>Telepon:</strong><br>(021) 1234-5678</span></li>
                                <li class="d-flex align-items-start"><i class="bi bi-envelope-fill fs-4 me-3 text-primary"></i> <span><strong>Email:</strong><br>info@grandluxe.test</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold mb-3">Kirim Pesan</h5>
                            @livewire('public.contact-form')
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-4">
        <div class="container text-center text-md-start">
            <div class="row text-center text-md-start">
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold text-primary">Grand Luxe</h5>
                    <p>Pengalaman menginap mewah yang mendefinisikan kembali kenyamanan dan keanggunan.</p>
                </div>
                <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold">Navigasi</h5>
                    <p><a href="#alasan" class="text-white" style="text-decoration: none;">Tentang</a></p>
                    <p><a href="#fasilitas" class="text-white" style="text-decoration: none;">Fasilitas</a></p>
                    <p><a href="#galeri" class="text-white" style="text-decoration: none;">Galeri</a></p>
                </div>
                <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
                     <h5 class="text-uppercase mb-4 fw-bold">Kontak</h5>
                     <p><i class="bi bi-house-door-fill me-3"></i>Jl. Kemewahan No. 1, Jakarta</p>
                     <p><i class="bi bi-envelope-fill me-3"></i>info@grandluxe.test</p>
                     <p><i class="bi bi-telephone-fill me-3"></i>(021) 1234-5678</p>
                </div>
            </div>
            <hr class="mb-4">
            <div class="row align-items-center">
                <div class="col-md-7 col-lg-8">
                    <p>Hak Cipta ©{{ date('Y') }} <strong>Grand Luxe</strong>. Seluruh hak cipta dilindungi.</p>
                </div>
                <div class="col-md-5 col-lg-4">
                    <div class="text-center text-md-end">
                        <ul class="list-unstyled list-inline">
                            <li class="list-inline-item">
                                <a href="#" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="bi bi-facebook"></i></a>
                            </li>
                             <li class="list-inline-item">
                                <a href="#" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="bi bi-twitter-x"></i></a>
                            </li>
                             <li class="list-inline-item">
                                <a href="#" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="bi bi-instagram"></i></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>


    @push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sections = document.querySelectorAll('.fade-in-section');

            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1
            });

            sections.forEach(section => {
                observer.observe(section);
            });
        });
    </script>
    @endpush

</x-layouts.public>
