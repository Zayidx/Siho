<!DOCTYPE html>
<html lang="id" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    
    {{-- Tailwind CSS: CDN in local, static CSS in non-local env (no Vite) --}}
    @env('local')
        <script src="https://cdn.tailwindcss.com"></script>
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/tailwind.css') }}">
    @endenv
    
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    {{-- Flatpickr for date inputs --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    {{-- Livewire Styles --}}
    @livewireStyles
    
    {{-- Custom Styles from child views --}}
    @stack('styles')

    <script>
        // Konfigurasi awal Tailwind untuk dark mode
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <style>
        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-200 antialiased">
    
    {{-- Header/Navbar --}}
    <nav id="mainNav" class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50 transition-shadow duration-300">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a class="text-2xl font-bold text-gray-900 dark:text-white" href="/">{{ config('app.name', 'Grand Luxe') }}</a>
                
                {{-- Desktop Nav --}}
                <div class="hidden md:flex items-center space-x-1">
                    <a href="/" class="nav-link px-3 py-2 rounded-md text-sm font-medium">Beranda</a>
                    <a href="/#fasilitas" class="nav-link px-3 py-2 rounded-md text-sm font-medium">Fasilitas</a>
                    <a href="/#harga" class="nav-link px-3 py-2 rounded-md text-sm font-medium">Harga</a>
                    <a href="/#testimoni" class="nav-link px-3 py-2 rounded-md text-sm font-medium">Testimoni</a>
                    <a href="/#galeri" class="nav-link px-3 py-2 rounded-md text-sm font-medium">Galeri</a>
                    <a href="/#faq" class="nav-link px-3 py-2 rounded-md text-sm font-medium">FAQ</a>
                    <a href="/#kontak" class="nav-link px-3 py-2 rounded-md text-sm font-medium">Kontak</a>
                    <a href="{{ route('menu') }}" class="nav-link px-3 py-2 rounded-md text-sm font-medium">Menu</a>
                    <a href="{{ route('booking.hotel') }}" class="nav-link px-3 py-2 rounded-md text-sm font-medium">Pesan</a>
                </div>

                {{-- Right side actions --}}
                <div class="hidden md:flex items-center space-x-3">
                    <button id="themeToggle" class="px-2 py-1 text-gray-500 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400" type="button" title="Toggle theme">
                        <i class="bi bi-moon-stars"></i>
                    </button>
                    <button id="badgeModeBtn" class="px-2 py-1 text-gray-500 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400" type="button" title="Tampilkan jumlah Qty atau Items di badge keranjang">Mode: Qty</button>
                    @auth
                        @php
                            $roleName = optional(Auth::user()->role)->name;
                            $dashboardUrl = $roleName === 'superadmin' ? route('admin.dashboard') : route('user.dashboard');
                        @endphp
                        <a class="relative px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-md hover:bg-green-700" href="{{ route('menu') }}">
                            Pesan Makanan
                            @php($cartCount = array_sum(array_values(session('fnb_cart', []))))
                            <span id="fnbCartBadge" class="absolute -top-2 -right-2 text-[11px] px-1.5 py-0.5 rounded-full bg-red-600 text-white {{ $cartCount ? '' : 'hidden' }}">{{ $cartCount ?: '' }}</span>
                        </a>
                        <a class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700" href="{{ $dashboardUrl }}">Dashboard</a>
                        <a href="javascript:void(0)" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-md dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Keluar</a>
                    @else
                        <a class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-md hover:bg-green-700" href="{{ route('menu') }}">Pesan Makanan</a>
                        <a class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700" href="{{ route('login') }}">Masuk</a>
                        <a class="px-4 py-2 text-sm font-semibold text-blue-600 border border-blue-600 rounded-md hover:bg-blue-50 dark:hover:bg-gray-800" href="{{ route('register') }}">Daftar</a>
                    @endauth
                </div>

                {{-- Mobile Menu Button --}}
                <div class="md:hidden">
                    <button id="mobileMenuButton" class="inline-flex items-center justify-center p-2 text-gray-400 rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                        <span class="sr-only">Buka menu utama</span>
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div class="hidden md:hidden" id="mobileMenu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="/" class="nav-link block px-3 py-2 rounded-md text-base font-medium">Beranda</a>
                <a href="/#fasilitas" class="nav-link block px-3 py-2 rounded-md text-base font-medium">Fasilitas</a>
                <a href="/#harga" class="nav-link block px-3 py-2 rounded-md text-base font-medium">Harga</a>
                <a href="/#testimoni" class="nav-link block px-3 py-2 rounded-md text-base font-medium">Testimoni</a>
                <a href="/#galeri" class="nav-link block px-3 py-2 rounded-md text-base font-medium">Galeri</a>
                <a href="/#faq" class="nav-link block px-3 py-2 rounded-md text-base font-medium">FAQ</a>
                <a href="/#kontak" class="nav-link block px-3 py-2 rounded-md text-base font-medium">Kontak</a>
                <a href="{{ route('menu') }}" class="nav-link block px-3 py-2 rounded-md text-base font-medium">Menu</a>
                <a href="{{ route('booking.hotel') }}" class="nav-link block px-3 py-2 rounded-md text-base font-medium">Pesan</a>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <button id="themeToggleMobile" class="flex-grow px-2 py-1 text-gray-500 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400" type="button" title="Toggle theme">
                        <i class="bi bi-moon-stars"></i>
                    </button>
                    <button id="badgeModeBtnMobile" class="flex-grow px-2 py-1 text-gray-500 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400" type="button" title="Tampilkan jumlah Qty atau Items di badge keranjang">Mode: Qty</button>
                    @auth
                        @php
                            $roleName = optional(Auth::user()->role)->name;
                            $dashboardUrl = $roleName === 'superadmin' ? route('admin.dashboard') : route('user.dashboard');
                        @endphp
                        <a class="relative flex-grow w-full px-4 py-2 text-sm font-semibold text-center text-white bg-green-600 rounded-md hover:bg-green-700" href="{{ route('menu') }}">
                            Pesan Makanan
                            @php($cartCount = array_sum(array_values(session('fnb_cart', []))))
                            <span id="fnbCartBadgeMobile" class="absolute -top-2 right-2 text-[11px] px-1.5 py-0.5 rounded-full bg-red-600 text-white {{ $cartCount ? '' : 'hidden' }}">{{ $cartCount ?: '' }}</span>
                        </a>
                        <a class="flex-grow w-full px-4 py-2 text-sm font-semibold text-center text-white bg-blue-600 rounded-md hover:bg-blue-700" href="{{ $dashboardUrl }}">Dashboard</a>
                        <a href="javascript:void(0)" class="flex-grow w-full px-4 py-2 text-sm font-semibold text-center text-gray-700 bg-gray-200 rounded-md dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Keluar</a>
                    @else
                        <a class="flex-grow w-full px-4 py-2 text-sm font-semibold text-center text-white bg-green-600 rounded-md hover:bg-green-700" href="{{ route('menu') }}">Pesan Makanan</a>
                        <a class="flex-grow w-full px-4 py-2 text-sm font-semibold text-center text-white bg-blue-600 rounded-md hover:bg-blue-700" href="{{ route('login') }}">Masuk</a>
                        <a class="flex-grow w-full px-4 py-2 text-sm font-semibold text-center text-blue-600 border border-blue-600 rounded-md hover:bg-blue-50 dark:hover:bg-gray-800" href="{{ route('register') }}">Daftar</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Hidden global logout form --}}
    @auth
    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">@csrf</form>
    @endauth

    {{-- Livewire Scripts --}}
    @livewireScripts
    
    {{-- Other Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <script>
        // Initial F&B cart counts from session
        window._fnbCounts = {
            qty: {{ array_sum(array_values(session('fnb_cart', []))) }},
            items: {{ is_array(session('fnb_cart')) ? count(session('fnb_cart')) : 0 }}
        };
        window.setFnbBadgeMode = function(mode){ try{ localStorage.setItem('fnbBadgeMode', (mode==='items'?'items':'qty')); window._fnbUpdateBadgeFromCounts && window._fnbUpdateBadgeFromCounts(); }catch(e){} };
        document.addEventListener('DOMContentLoaded', () => {
            // Theme Toggle Logic
            (function() {
                const themeToggles = document.querySelectorAll('#themeToggle, #themeToggleMobile');
                const htmlEl = document.documentElement;
                const key = 'theme';

                const applyTheme = (theme) => {
                    if (theme === 'dark') {
                        htmlEl.classList.add('dark');
                    } else {
                        htmlEl.classList.remove('dark');
                    }
                    themeToggles.forEach(btn => {
                        const icon = btn.querySelector('i');
                        if (icon) {
                            icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
                        }
                    });
                };

                const savedTheme = localStorage.getItem(key);
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const initialTheme = savedTheme || (prefersDark ? 'dark' : 'light');
                applyTheme(initialTheme);

                themeToggles.forEach(btn => {
                    btn.addEventListener('click', () => {
                        const isDark = htmlEl.classList.contains('dark');
                        const newTheme = isDark ? 'light' : 'dark';
                        localStorage.setItem(key, newTheme);
                        applyTheme(newTheme);
                    });
                });
            })();

            // Mobile Menu Toggle
            (function() {
                const mobileMenuButton = document.getElementById('mobileMenuButton');
                const mobileMenu = document.getElementById('mobileMenu');
                if (mobileMenuButton && mobileMenu) {
                    mobileMenuButton.addEventListener('click', () => {
                        mobileMenu.classList.toggle('hidden');
                    });
                }
            })();
            
            // SweetAlert Session Messages
            const success = @json(session('success'));
            const error = @json(session('error'));
            if (success) Swal.fire({ icon: 'success', title: 'Berhasil', text: success, timer: 2500, showConfirmButton: false });
            if (error) Swal.fire({ icon: 'error', title: 'Gagal', text: error, confirmButtonText: 'Tutup' });
        });

        // Livewire SweetAlert Listeners
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal:success', e => Swal.fire({ icon: 'success', title: 'Berhasil', text: e.message, timer: 2500, showConfirmButton: false }));
            Livewire.on('swal:error', e => Swal.fire({ icon: 'error', title: 'Gagal', text: e.message, confirmButtonText: 'Tutup' }));
            Livewire.on('swal:info', e => Swal.fire({ icon: 'info', title: 'Info', text: e.message, timer: 2200, showConfirmButton: false }));
        });

        // Active Nav Link Highlighting on Scroll
        (function() {
            if (window.location.pathname !== '/') return;

            const sections = document.querySelectorAll('main section[id]');
            const navLinks = document.querySelectorAll('nav .nav-link');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        navLinks.forEach(link => {
                            link.classList.remove('text-blue-600', 'dark:text-blue-400', 'font-semibold');
                            link.classList.add('text-gray-600', 'dark:text-gray-300', 'hover:text-gray-900', 'dark:hover:text-white');
                            
                            let href = link.getAttribute('href');
                            // Handle both absolute and relative URLs for homepage anchors
                            if (href.includes('/#') && href.substring(href.indexOf('#') + 1) === entry.target.id) {
                                link.classList.add('text-blue-600', 'dark:text-blue-400', 'font-semibold');
                                link.classList.remove('text-gray-600', 'dark:text-gray-300');
                            }
                        });
                    }
                });
            }, { rootMargin: '-50% 0px -50% 0px' });

            sections.forEach(section => {
                observer.observe(section);
            });
        })();

        // Quick add menu (homepage) via session endpoint
        (function(){
            document.body.addEventListener('click', async (e) => {
                const btn = e.target.closest('.quick-add-menu');
                if (!btn) return;
                e.preventDefault();
                const id = btn.getAttribute('data-item-id');
                // disable button while processing
                const origHtml = btn.innerHTML; btn.disabled = true; btn.innerText = 'Menambah…';
                try {
                    const res = await fetch('{{ route('fnb.cart.add') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ menu_item_id: id, qty: 1 }),
                    });
                    if (!res.ok) throw new Error('Request failed');
                    // Update cart badge
                    const badge = document.getElementById('fnbCartBadge');
                    const badgeM = document.getElementById('fnbCartBadgeMobile');
                    const data = await res.json();
                    if (data && typeof data === 'object' && 'qty' in data && 'items' in data) {
                        window._fnbCounts = { qty: parseInt(data.qty)||0, items: parseInt(data.items)||0 };
                        window._fnbUpdateBadgeFromCounts && window._fnbUpdateBadgeFromCounts();
                    }
                    Livewire?.dispatch('swal:success', { message: 'Ditambahkan ke keranjang' });
                } catch(err) {
                    Livewire?.dispatch('swal:error', { message: 'Gagal menambah ke keranjang' });
                } finally { btn.disabled = false; btn.innerHTML = origHtml; }
            });
        })();

        // Livewire cart badge listeners (for /menu add/remove/checkout)
        (function(){
            const mode = () => { try{ return localStorage.getItem('fnbBadgeMode') || 'qty'; }catch(e){ return 'qty'; } };
            const updateBadgeFromCounts = () => {
                const m = mode();
                const v = m==='items' ? (window._fnbCounts?.items||0) : (window._fnbCounts?.qty||0);
                updateBadgeNumber(v);
            };
            // expose for global calls
            window._fnbUpdateBadgeFromCounts = updateBadgeFromCounts;
            const updateBadgeNumber = (n) => {
                const badge = document.getElementById('fnbCartBadge');
                const badgeM = document.getElementById('fnbCartBadgeMobile');
                [badge,badgeM].forEach(el => { if (!el) return; if (n<=0){ el.textContent=''; el.classList.add('hidden'); } else { el.textContent=String(n); el.classList.remove('hidden'); }});
            };
            const inc = () => {
                // Optimistic increment qty and items by 1
                window._fnbCounts = window._fnbCounts || {qty:0,items:0};
                window._fnbCounts.qty = (window._fnbCounts.qty||0)+1;
                window._fnbCounts.items = (window._fnbCounts.items||0)+1;
                updateBadgeFromCounts();
            };
            const reset = () => { window._fnbCounts = {qty:0,items:0}; updateBadgeNumber(0); };

            document.addEventListener('livewire:init', () => {
                Livewire.on('fnb:cart:inc', () => inc());
                Livewire.on('fnb:cart:reset', () => reset());
                Livewire.on('fnb:cart:update', (payload) => {
                    // payload may be number or {qty,items}
                    if (payload && typeof payload === 'object') {
                        window._fnbCounts = { qty: parseInt(payload.qty)||0, items: parseInt(payload.items)||0 };
                        updateBadgeFromCounts();
                    } else {
                        const v = parseInt(payload) || 0;
                        window._fnbCounts = window._fnbCounts || {qty:0,items:0};
                        window._fnbCounts.qty = v;
                        updateBadgeFromCounts();
                    }
                });
            });

            // Reset badge on logout submit
            const logoutForm = document.getElementById('logout-form');
            if (logoutForm) {
                logoutForm.addEventListener('submit', () => reset());
            }

            // Initialize badge from session counts and current mode
            updateBadgeFromCounts();

            // Badge mode toggle buttons
            const applyModeLabel = () => {
                const m = mode();
                const text = 'Mode: ' + (m==='items' ? 'Items' : 'Qty');
                const b1 = document.getElementById('badgeModeBtn');
                const b2 = document.getElementById('badgeModeBtnMobile');
                if (b1) b1.textContent = text; if (b2) b2.textContent = text;
            };
            applyModeLabel();
            const toggleMode = () => { const m = mode()==='items' ? 'qty' : 'items'; setFnbBadgeMode(m); applyModeLabel(); };
            const b1 = document.getElementById('badgeModeBtn');
            const b2 = document.getElementById('badgeModeBtnMobile');
            if (b1) b1.addEventListener('click', toggleMode);
            if (b2) b2.addEventListener('click', toggleMode);
        })();
    </script>
    
    {{-- Custom Scripts from child views --}}
    @stack('scripts')
</body>
</html>
