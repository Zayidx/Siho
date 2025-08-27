<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Area Pengguna' }}</title>
    <link rel="shortcut icon" href="{{ asset('assets/compiled/svg/favicon.svg') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/iconly.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @livewireStyles
</head>
<body>
    <script src="{{ asset('assets/static/js/initTheme.js') }}"></script>
    <div id="app">
        <div id="sidebar">
            <div class="sidebar-wrapper active">
                <div class="sidebar-header position-relative">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="logo">
                            <a href="{{ route('user.dashboard') }}"><img src="{{ asset('./assets/compiled/svg/logo.svg') }}" alt="Logo"></a>
                        </div>
                        <div class="sidebar-toggler x">
                            <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                        </div>
                    </div>
                </div>
                @include('components.layouts.partials.sidebar_user')
            </div>
        </div>
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="page-heading">
                <h3>{{ $title ?? 'Pengguna' }}</h3>
            </div>

            <div class="page-content">
                @php($user = Auth::user())
                @if($user && $user->pending_email)
                    <div id="emailVerifyAlert" class="alert alert-warning d-flex justify-content-between align-items-center" style="display:none;">
                        <div>
                            Email baru <strong>{{ $user->pending_email }}</strong> menunggu verifikasi. Cek inbox Anda.
                        </div>
                        <div>
                            <a href="{{ route('verification.resend') }}" class="btn btn-sm btn-outline-dark">Kirim Ulang Email Verifikasi</a>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="localStorage.setItem('hideEmailVerifyAlert','1'); document.getElementById('emailVerifyAlert').style.display='none'">Jangan tampilkan lagi</button>
                        </div>
                    </div>
                @endif
                {{ $slot }}
            </div>

            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-start">
                        <p>{{ date('Y') }} &copy; Grand Luxe</p>
                    </div>
                    <div class="float-end">
                        <p>Built with <span class="text-danger"><i class="bi bi-heart-fill icon-mid"></i></span></p>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    @livewireScripts
    <script src="{{ asset('assets/static/js/components/dark.js') }}"></script>
    <script src="{{ asset('assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/compiled/js/app.js') }}"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal:success', e => Swal.fire({ icon: 'success', title: 'Berhasil', text: e.message, timer: 2500, showConfirmButton: false }));
            Livewire.on('swal:error', e => Swal.fire({ icon: 'error', title: 'Gagal', text: e.message, confirmButtonText: 'Tutup' }));
            Livewire.on('swal:info', e => Swal.fire({ icon: 'info', title: 'Info', text: e.message, timer: 2200, showConfirmButton: false }));
        });

        // Tampilkan banner verifikasi email hanya jika user belum menyembunyikannya
        document.addEventListener('DOMContentLoaded', () => {
            try {
                const el = document.getElementById('emailVerifyAlert');
                if (!el) return;
                if (localStorage.getItem('hideEmailVerifyAlert') === '1') {
                    el.style.display = 'none';
                } else {
                    el.style.display = '';
                }
            } catch (e) {}
        });
    </script>
</body>
</html>
