<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @livewireStyles
    <style>body{background-color:#f8f9fa}</style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">Grand Luxe</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#pubNav" aria-controls="pubNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="pubNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" aria-current="page" href="/">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/#fasilitas">Fasilitas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/#galeri">Galeri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/#kontak">Kontak</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('booking.wizard') ? 'active' : '' }}" href="{{ route('booking.wizard') }}">Pesan</a>
                    </li>
                </ul>
                <div class="d-flex gap-2">
                    @auth
                        <a class="btn btn-outline-primary" href="{{ route('user.dashboard') }}">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-secondary">Keluar</button></form>
                    @else
                        <a class="btn btn-primary" href="{{ route('login') }}">Masuk</a>
                        <a class="btn btn-outline-primary" href="{{ route('register') }}">Daftar</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="py-4">
        {{ $slot }}
    </main>

    <footer class="border-top py-4 mt-auto">
        <div class="container text-center small text-muted">© {{ date('Y') }} Grand Luxe</div>
    </footer>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const success = @json(session('success'));
            const error = @json(session('error'));
            if (success) Swal.fire({ icon: 'success', title: 'Berhasil', text: success, timer: 2500, showConfirmButton: false });
            if (error) Swal.fire({ icon: 'error', title: 'Gagal', text: error, confirmButtonText: 'Tutup' });
        });
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal:success', e => Swal.fire({ icon: 'success', title: 'Berhasil', text: e.message, timer: 2500, showConfirmButton: false }));
            Livewire.on('swal:error', e => Swal.fire({ icon: 'error', title: 'Gagal', text: e.message, confirmButtonText: 'Tutup' }));
            Livewire.on('swal:info', e => Swal.fire({ icon: 'info', title: 'Info', text: e.message, timer: 2200, showConfirmButton: false }));
        });

        // Smooth scroll for same-page anchors on homepage
        (function() {
            const onHome = window.location.pathname === '/';
            if (!onHome) return;
            document.querySelectorAll('a.nav-link[href^="/#"]').forEach(a => {
                a.addEventListener('click', (e) => {
                    const id = a.getAttribute('href').slice(2);
                    const el = document.getElementById(id);
                    if (el) {
                        e.preventDefault();
                        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });

            // Active section highlighting
            const navMap = new Map();
            document.querySelectorAll('a.nav-link[href^="/#"]').forEach(a => {
                const id = a.getAttribute('href').slice(2);
                navMap.set(id, a);
            });
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    const link = navMap.get(entry.target.id);
                    if (!link) return;
                    if (entry.isIntersecting) {
                        document.querySelectorAll('a.nav-link').forEach(x => x.classList.remove('active'));
                        link.classList.add('active');
                    }
                });
            }, { root: null, rootMargin: '-30% 0px -60% 0px', threshold: 0.1 });
            navMap.forEach((_, id) => {
                const el = document.getElementById(id);
                if (el) observer.observe(el);
            });
        })();
    </script>
</body>
</html>
