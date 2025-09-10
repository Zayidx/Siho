<!DOCTYPE html>
<html lang="id" class="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="SIHO — Booking hotel, fasilitas unggulan, restoran, dan galeri. Nikmati pengalaman menginap terbaik dengan layanan 24/7.">
    <title>{{ $title ?? config('app.name') }}</title>
    <!-- No Bootstrap CSS (Tailwind-only navbar) -->
    <!-- Tailwind CSS (CDN) with darkMode=class -->
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            darkMode: 'class'
        };
    </script>
    <!-- Apply saved theme early to avoid flicker and ensure dark mode on all pages -->
    <script>
        (function() {
            try {
                var htmlEl = document.documentElement;
                var saved = localStorage.getItem('theme');
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var theme = saved || (prefersDark ? 'dark' : 'light');
                if (theme === 'dark') {
                    htmlEl.classList.add('dark');
                    htmlEl.setAttribute('data-bs-theme', 'dark');
                } else {
                    htmlEl.classList.remove('dark');
                    htmlEl.setAttribute('data-bs-theme', 'light');
                }
            } catch (e) {
                /* no-op */
            }
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Flatpickr for date inputs -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Livewire Styles -->
    @livewireStyles

    <!-- Custom Styles from child views -->
    @stack('styles')
    <style>
        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }

        /* Skip link accessibility helper */
        .visually-hidden-focusable:not(:focus):not(:active) {
            position: absolute !important;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        /* Fallback display rules for navbar visibility */
        #navDesktop {
            display: none;
        }

        @media (min-width: 768px) {
            #navDesktop {
                display: flex !important;
            }
        }

        /* Ensure mobile overlay menu is hidden on desktop */
        @media (min-width: 768px) {
            #navMenu {
                display: none !important;
            }
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-200 antialiased">
    <a href="#mainContent"
        class="skip-link visually-hidden-focusable fixed top-0 left-0 m-2 p-2 bg-white border rounded text-gray-900">Lewati
        ke Konten</a>
    @php($cartCount = (int) array_sum(array_values(session('fnb_cart', []))))

    <!-- Header/Navbar (native CSS) -->
    <nav id="mainNav"
        class="sticky top-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex h-16 items-center justify-between">
                <a class="font-bold text-lg text-gray-900 dark:text-gray-100"
                    href="/">{{ config('app.name', 'SIHO') }}</a>
                @php($__isAuth = \Illuminate\Support\Facades\Auth::check())
                @php($__dashUrl = $__isAuth ? (optional(\Illuminate\Support\Facades\Auth::user()->role)->name === 'superadmin' ? route('admin.dashboard') : route('user.dashboard')) : null)
                <!-- Desktop inline menu -->
                <div id="navDesktop" class="hidden md:flex items-center gap-6 flex-1 ml-10">
                    <ul class="flex items-center gap-2">
                        <li><a class="nav-link block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                                href="/">Beranda</a></li>
                        <li><a class="nav-link block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                                href="/#fasilitas">Fasilitas</a></li>
                        <li><a class="nav-link block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                                href="/#harga">Harga</a></li>
                        <li><a class="nav-link block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                                href="/#testimoni">Testimoni</a></li>
                        <li><a class="nav-link block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                                href="/#galeri">Galeri</a></li>
                        <li><a class="nav-link block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                                href="/#faq">FAQ</a></li>
                        <li><a class="nav-link block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                                href="/#kontak">Kontak</a></li>
                        <li><a class="nav-link block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                                href="{{ route('menu') }}">Menu</a></li>
                        <li><a class="nav-link block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                                href="{{ route('booking.hotel') }}">Pesan</a></li>
                    </ul>
                    <div class="flex items-center gap-2 ml-auto">
                        <button id="themeToggle"
                            class="inline-flex items-center justify-center px-3 py-2 rounded border border-gray-400/60 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                            type="button" title="Ubah tema" aria-label="Ubah tema">
                            <i class="bi bi-moon-stars" aria-hidden="true"></i>
                        </button>
                        <!-- Auth-only buttons -->
                        <a class="relative inline-flex items-center px-3 py-2 rounded bg-green-600 text-white hover:bg-green-700 {{ $__isAuth ? '' : 'hidden' }}"
                            href="{{ route('menu') }}">
                            Pesan Makanan
                            <span id="fnbCartBadge"
                                class="absolute -top-1 -right-1 bg-red-600 text-white text-[11px] font-semibold rounded-full px-1.5 py-0.5 {{ $cartCount ? '' : 'hidden' }}">{{ $cartCount ?: '' }}</span>
                        </a>
                        <a class="inline-flex items-center px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 {{ $__isAuth ? '' : 'hidden' }}"
                            href="{{ $__dashUrl }}">Dashboard</a>
                        <a href="#"
                            class="inline-flex items-center px-3 py-2 rounded border border-gray-400/60 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 {{ $__isAuth ? '' : 'hidden' }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Keluar</a>
                        <!-- Guest-only buttons -->
                        <a class="inline-flex items-center px-3 py-2 rounded bg-green-600 text-white hover:bg-green-700 {{ $__isAuth ? 'hidden' : '' }}"
                            href="{{ route('menu') }}">Pesan Makanan</a>
                        <a class="inline-flex items-center px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 {{ $__isAuth ? 'hidden' : '' }}"
                            href="{{ route('login') }}">Masuk</a>
                        <a class="inline-flex items-center px-3 py-2 rounded border border-gray-400/60 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 {{ $__isAuth ? 'hidden' : '' }}"
                            href="{{ route('register') }}">Daftar</a>
                    </div>
                </div>
                <div class="flex items-center gap-2 md:hidden">
                    <button id="themeToggleMobile"
                        class="inline-flex items-center justify-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800"
                        type="button" title="Ubah tema" aria-label="Ubah tema">
                        <i class="bi bi-moon-stars" aria-hidden="true"></i>
                    </button>
                    <button id="navBurger"
                        class="inline-flex items-center justify-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800"
                        type="button" aria-controls="navMenu" aria-expanded="false" aria-label="Buka menu">
                        <span class="sr-only">Buka menu</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div id="navMenu"
                class="hidden md:hidden absolute left-0 right-0 top-16 bg-white/95 dark:bg-gray-900/95 border-t border-gray-200 dark:border-gray-700 shadow-lg">
                <ul class="flex flex-col gap-1 p-3">
                    <li><a class="nav-link block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                            href="/">Beranda</a></li>
                    <li><a class="nav-link block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                            href="/#fasilitas">Fasilitas</a></li>
                    <li><a class="nav-link block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                            href="/#harga">Harga</a></li>
                    <li><a class="nav-link block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                            href="/#testimoni">Testimoni</a></li>
                    <li><a class="nav-link block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                            href="/#galeri">Galeri</a></li>
                    <li><a class="nav-link block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                            href="/#faq">FAQ</a></li>
                    <li><a class="nav-link block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                            href="/#kontak">Kontak</a></li>
                    <li><a class="nav-link block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                            href="{{ route('menu') }}">Menu</a></li>
                    <li><a class="nav-link block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                            href="{{ route('booking.hotel') }}">Pesan</a></li>
                </ul>
                <div class="flex items-center gap-2 p-3 border-t border-gray-200 dark:border-gray-700">
                    @php($__isAuth = \Illuminate\Support\Facades\Auth::check())
                    @php($__dashUrl = $__isAuth ? (optional(\Illuminate\Support\Facades\Auth::user()->role)->name === 'superadmin' ? route('admin.dashboard') : route('user.dashboard')) : null)
                    <!-- Auth-only buttons -->
                    <a class="relative inline-flex items-center px-3 py-2 rounded bg-green-600 text-white hover:bg-green-700 {{ $__isAuth ? '' : 'hidden' }}"
                        href="{{ route('menu') }}">
                        Pesan Makanan
                        <span id="fnbCartBadgeMobile"
                            class="absolute -top-1 -right-1 bg-red-600 text-white text-[11px] font-semibold rounded-full px-1.5 py-0.5 {{ $cartCount ? '' : 'hidden' }}">{{ $cartCount ?: '' }}</span>
                    </a>
                    <a class="inline-flex items-center px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 {{ $__isAuth ? '' : 'hidden' }}"
                        href="{{ $__dashUrl }}">Dashboard</a>
                    <a href="#"
                        class="inline-flex items-center px-3 py-2 rounded border border-gray-400/60 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 {{ $__isAuth ? '' : 'hidden' }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Keluar</a>
                    <!-- Guest-only buttons -->
                    <a class="inline-flex items-center px-3 py-2 rounded bg-green-600 text-white hover:bg-green-700 {{ $__isAuth ? 'hidden' : '' }}"
                        href="{{ route('menu') }}">Pesan Makanan</a>
                    <a class="inline-flex items-center px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 {{ $__isAuth ? 'hidden' : '' }}"
                        href="{{ route('login') }}">Masuk</a>
                    <a class="inline-flex items-center px-3 py-2 rounded border border-gray-400/60 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 {{ $__isAuth ? 'hidden' : '' }}"
                        href="{{ route('register') }}">Daftar</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main id="mainContent" tabindex="-1">
        {{ $slot }}
    </main>

    <!-- Hidden global logout form -->
    @auth
        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">@csrf</form>
    @endauth

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Other Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" defer></script>

    <!-- Initial data for JS (avoid Blade tokens in JS) -->
    <script type="application/json" id="fnb-init">
        {!! json_encode([
            'qty' => array_sum(array_values(session('fnb_cart', []))),
            'items' => (is_array(session('fnb_cart')) ? count(session('fnb_cart')) : 0),
            'success' => session('success'),
            'error' => session('error'),
            'cartAdd' => route('fnb.cart.add'),
            'csrf' => csrf_token(),
        ]) !!}
    </script>

    <script>
        // Initial F&B cart counts from session
        (function() {
            var INIT = {
                qty: 0,
                items: 0,
                success: null,
                error: null
            };
            try {
                var initEl = document.getElementById('fnb-init');
                if (initEl && initEl.textContent) {
                    var parsed = JSON.parse(initEl.textContent);
                    if (parsed && typeof parsed === 'object') {
                        INIT = Object.assign(INIT, parsed);
                    }
                }
            } catch (e) {}
            window._fnbCounts = {
                qty: Number(INIT.qty) || 0,
                items: Number(INIT.items) || 0
            };
            window._fnbFlashSuccess = INIT.success;
            window._fnbFlashError = INIT.error;
            window._fnbInit = INIT;
        })();
        window.setFnbBadgeMode = function(mode) {
            try {
                localStorage.setItem('fnbBadgeMode', (mode === 'items' ? 'items' : 'qty'));
                window._fnbUpdateBadgeFromCounts && window._fnbUpdateBadgeFromCounts();
            } catch (e) {}
        };

        document.addEventListener('DOMContentLoaded', () => {
            // Theme Toggle Logic (robust against Livewire nav & re-renders)
            (function() {
                const htmlEl = document.documentElement;
                const key = 'theme';

                function updateToggleIcons(theme) {
                    const toggles = document.querySelectorAll('#themeToggle, #themeToggleMobile');
                    toggles.forEach(function(btn) {
                        var i = btn && btn.querySelector ? btn.querySelector('i') : null;
                        if (i) {
                            i.className = (theme === 'dark') ? 'bi bi-sun' : 'bi bi-moon-stars';
                        }
                    });
                }

                function applyTheme(theme) {
                    if (theme === 'dark') htmlEl.classList.add('dark');
                    else htmlEl.classList.remove('dark');
                    htmlEl.setAttribute('data-bs-theme', theme === 'dark' ? 'dark' : 'light');
                    updateToggleIcons(theme);
                }

                // Initialize icons based on current theme (set earlier in <head>)
                updateToggleIcons(htmlEl.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light');

                // Delegate clicks so listeners survive DOM changes
                document.addEventListener('click', function(e) {
                    const btn = e.target.closest('#themeToggle, #themeToggleMobile');
                    if (!btn) return;
                    const nowDark = htmlEl.getAttribute('data-bs-theme') === 'dark';
                    const next = nowDark ? 'light' : 'dark';
                    try {
                        localStorage.setItem(key, next);
                    } catch (e) {}
                    applyTheme(next);
                });

                // Re-sync icons if Livewire navigates between views
                document.addEventListener('livewire:navigated', function() {
                    updateToggleIcons(htmlEl.getAttribute('data-bs-theme') === 'dark' ? 'dark' :
                        'light');
                });
            })();

            // SweetAlert Session Messages
            const success = window._fnbFlashSuccess;
            const error = window._fnbFlashError;
            if (success) Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: success,
                timer: 2500,
                showConfirmButton: false
            });
            if (error) Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: error,
                confirmButtonText: 'Tutup'
            });
        });

        // Livewire SweetAlert Listeners
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal:success', e => Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: e.message,
                timer: 2500,
                showConfirmButton: false
            }));
            Livewire.on('swal:error', e => Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: e.message,
                confirmButtonText: 'Tutup'
            }));
            Livewire.on('swal:info', e => Swal.fire({
                icon: 'info',
                title: 'Info',
                text: e.message,
                timer: 2200,
                showConfirmButton: false
            }));
        });

        // Active Nav Link Highlighting on Scroll
        (function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('nav .nav-link');
            const activeClasses = ['text-blue-600', 'dark:text-blue-400', 'font-semibold'];

            function setActive(link, isActive) {
                link.classList.toggle('active', isActive);
                activeClasses.forEach(c => link.classList.toggle(c, isActive));
            }

            // Highlight link based on exact path first
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    setActive(link, true);
                }
            });

            // If on homepage, activate scroll-based highlighting
            if (currentPath === '/') {
                const sections = document.querySelectorAll('main section[id]');
                const observer = new IntersectionObserver(function(entries) {
                    let lastIntersectingId = null;
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            lastIntersectingId = entry.target.id;
                        }
                    });

                    navLinks.forEach(function(link) {
                        const href = link.getAttribute('href') || '';
                        const idx = href.indexOf('#');
                        if (idx === -1) return;
                        const linkTargetId = href.substring(idx + 1);
                        const isTarget = linkTargetId === lastIntersectingId;
                        setActive(link, isTarget);
                    });
                }, {
                    rootMargin: '-50% 0px -50% 0px'
                });

                sections.forEach(section => {
                    observer.observe(section);
                });
            }
        })();

        // Mobile burger toggle + desktop visibility sync
        (function() {
            var btn = document.getElementById('navBurger');
            var menu = document.getElementById('navMenu');
            var mq = window.matchMedia('(min-width: 768px)');

            function sync() {
                if (!menu) return;
                if (mq.matches) {
                    // Always hide mobile menu on desktop
                    btn?.setAttribute('aria-expanded', 'false');
                    menu.classList.add('hidden');
                } else {
                    const expanded = btn?.getAttribute('aria-expanded') === 'true';
                    menu.classList.toggle('hidden', !expanded);
                }
            }

            if (btn) {
                btn.addEventListener('click', function() {
                    var expanded = btn.getAttribute('aria-expanded') === 'true';
                    btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                    sync();
                });
            }
            if (mq.addEventListener) mq.addEventListener('change', sync);
            else window.addEventListener('resize', sync);
            sync();
        })();

        // Quick add menu (homepage) via session endpoint
        (function() {
            document.body.addEventListener('click', async function(e) {
                const btn = e.target.closest ? e.target.closest('.quick-add-menu') : null;
                if (!btn) return;
                e.preventDefault();
                const id = btn.getAttribute('data-item-id');
                const origHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerText = 'Menambah…';
                try {
                    const res = await fetch((window._fnbInit && _fnbInit.cartAdd) ? _fnbInit.cartAdd :
                        '/fnb/cart/add', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': (window._fnbInit && _fnbInit.csrf) ? _fnbInit.csrf : ''
                            },
                            body: JSON.stringify({
                                menu_item_id: id,
                                qty: 1
                            }),
                        });
                    if (!res.ok) throw new Error('Request failed');
                    const data = await res.json();
                    if (data && typeof data === 'object' && 'qty' in data && 'items' in data) {
                        window._fnbCounts = {
                            qty: parseInt(data.qty) || 0,
                            items: parseInt(data.items) || 0
                        };
                        if (window._fnbUpdateBadgeFromCounts) {
                            window._fnbUpdateBadgeFromCounts();
                        }
                    }
                    if (window.Livewire && window.Livewire.dispatch) {
                        window.Livewire.dispatch('swal:success', {
                            message: 'Ditambahkan ke keranjang'
                        });
                    }
                } catch (err) {
                    console.error('Quick add menu error:', err);
                    if (window.Livewire && window.Livewire.dispatch) {
                        window.Livewire.dispatch('swal:error', {
                            message: 'Gagal menambah ke keranjang'
                        });
                    }
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                }
            });
        })();

        // Livewire cart badge listeners
        (function() {
            function mode() {
                try {
                    return localStorage.getItem('fnbBadgeMode') || 'qty';
                } catch (e) {
                    return 'qty';
                }
            }

            function updateBadgeFromCounts() {
                var m = mode();
                var v = 0;
                if (m === 'items') {
                    v = (window._fnbCounts && window._fnbCounts.items) ? window._fnbCounts.items : 0;
                } else {
                    v = (window._fnbCounts && window._fnbCounts.qty) ? window._fnbCounts.qty : 0;
                }
                updateBadgeNumber(v);
            }
            window._fnbUpdateBadgeFromCounts = updateBadgeFromCounts;

            function updateBadgeNumber(n) {
                var nodes = [
                    document.getElementById('fnbCartBadge'),
                    document.getElementById('fnbCartBadgeMobile')
                ].filter(function(el) {
                    return !!el;
                });
                if (nodes.length === 0) return;
                nodes.forEach(function(badge) {
                    if (n <= 0) {
                        badge.textContent = '';
                        badge.classList.add('hidden');
                    } else {
                        badge.textContent = String(n);
                        badge.classList.remove('hidden');
                    }
                });
            }

            function reset() {
                window._fnbCounts = {
                    qty: 0,
                    items: 0
                };
                updateBadgeNumber(0);
            }

            document.addEventListener('livewire:init', function() {
                Livewire.on('fnb:cart:inc', function() {
                    window._fnbCounts = window._fnbCounts || {
                        qty: 0,
                        items: 0
                    };
                    window._fnbCounts.qty = (window._fnbCounts.qty || 0) + 1;
                    window._fnbCounts.items = (window._fnbCounts.items || 0) + 1;
                    updateBadgeFromCounts();
                });
                Livewire.on('fnb:cart:reset', function() {
                    reset();
                });
                Livewire.on('fnb:cart:update', function(payload) {
                    if (payload && typeof payload === 'object') {
                        window._fnbCounts = {
                            qty: parseInt(payload.qty) || 0,
                            items: parseInt(payload.items) || 0
                        };
                    } else {
                        window._fnbCounts = {
                            qty: parseInt(payload) || 0,
                            items: window._fnbCounts.items
                        };
                    }
                    updateBadgeFromCounts();
                });
            });

            var logoutForm = document.getElementById('logout-form');
            if (logoutForm) {
                logoutForm.addEventListener('submit', function() {
                    reset();
                });
            }
            updateBadgeFromCounts();
        })();
    </script>

    @stack('scripts')
</body>

</html>
