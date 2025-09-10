<!-- Hero Section -->
<header class="relative text-center text-white bg-center bg-cover"
    style="background-image: linear-gradient(rgba(13, 37, 63, 0.6), rgba(13, 37, 63, 0.6)), url('https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1350&q=80');">
    <div class="container relative z-10 px-4 py-20 sm:py-28 md:py-32 mx-auto">
        <h1 class="mb-3 text-3xl sm:text-4xl md:text-5xl font-bold" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
            Selamat Datang di {{ config('app.name', 'Grand Luxe') }}</h1>
        <p class="max-w-xl mx-auto mb-6 text-base sm:text-lg leading-relaxed">Nikmati pengalaman menginap tak terlupakan
            dengan layanan bintang lima dan kemewahan tiada tara.</p>
        <div class="flex flex-col items-center justify-center gap-3 mb-6 sm:flex-row">
            <a id="cta-booking" data-base="{{ route('booking.hotel') }}"
                href="{{ route('booking.hotel', request()->only(['checkin', 'checkout'])) }}"
                class="inline-flex items-center justify-center w-full px-6 py-3 font-semibold text-white transition duration-300 ease-in-out bg-blue-600 border border-transparent rounded-md shadow-sm sm:w-auto hover:bg-blue-700">
                <i class="mr-2 bi bi-calendar2-check"></i>Pesan Sekarang
            </a>
            <a href="{{ route('menu') }}"
                class="inline-flex items-center justify-center w-full px-6 py-3 font-semibold text-white transition duration-300 ease-in-out bg-transparent border border-white rounded-md shadow-sm sm:w-auto hover:bg-white hover:text-gray-900">
                <i class="mr-2 bi bi-egg-fried"></i>Pesan Makanan
            </a>
            <a href="#fasilitas"
                class="inline-flex items-center justify-center w-full px-6 py-3 font-semibold text-white transition duration-300 ease-in-out bg-transparent border border-white rounded-md shadow-sm sm:w-auto hover:bg-white hover:text-gray-900">
                <i class="mr-2 bi bi-gem"></i>Lihat Fasilitas
            </a>
        </div>

        <!-- Tanggal Check-in/Check-out (opsional untuk memperketat statistik & mempermudah booking) -->
        <form id="dateRangeForm" method="GET" action="{{ route('home') }}"
            class="mx-auto max-w-3xl grid grid-cols-1 gap-3 sm:grid-cols-5 bg-white/90 dark:bg-white/80 rounded-md p-3 sm:p-4 shadow-lg"
            autocomplete="off">
            <div class="sm:col-span-2">
                <label for="checkin" class="block text-xs font-semibold text-gray-700">Check‑in</label>
                <input type="date" id="checkin" name="checkin"
                    value="{{ request('checkin', now()->addDay()->toDateString()) }}" min="{{ now()->toDateString() }}"
                    class="w-full px-3 py-2 mt-1 text-gray-900 bg-white border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="sm:col-span-2">
                <label for="checkout" class="block text-xs font-semibold text-gray-700">Check‑out</label>
                <input type="date" id="checkout" name="checkout"
                    value="{{ request('checkout', now()->addDays(2)->toDateString()) }}"
                    min="{{ request('checkin') ?: now()->addDay()->toDateString() }}"
                    class="w-full px-3 py-2 mt-1 text-gray-900 bg-white border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-end sm:col-span-1 gap-2">
                <button type="submit"
                    class="w-full inline-flex items-center justify-center px-4 py-2.5 font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700">Cek</button>
            </div>
            <div id="daterange-error" class="sm:col-span-5 hidden text-red-100 bg-red-600/80 rounded p-2 text-sm">
                Tanggal tidak valid. Pastikan check‑out setelah check‑in.</div>
            <div class="sm:col-span-5 text-center text-[12px] text-gray-100">Statistik di bawah akan menyesuaikan
                tanggal terpilih.</div>
            <div class="sm:col-span-5 text-center text-[12px] text-gray-100">
                <a id="cta-booking-2" data-base="{{ route('booking.hotel') }}"
                    href="{{ route('booking.hotel', request()->only(['checkin', 'checkout'])) }}"
                    class="underline font-semibold">Lanjut Booking</a>
            </div>
        </form>

        @push('scripts')
            <script>
                (function() {
                    const ci = document.getElementById('checkin');
                    const co = document.getElementById('checkout');
                    const form = document.getElementById('dateRangeForm');
                    const err = document.getElementById('daterange-error');
                    const cta1 = document.getElementById('cta-booking');
                    const cta2 = document.getElementById('cta-booking-2');
                    const kIn = 'home.checkin';
                    const kOut = 'home.checkout';

                    function fmt(d) {
                        const y = d.getFullYear(),
                            m = String(d.getMonth() + 1).padStart(2, '0'),
                            day = String(d.getDate()).padStart(2, '0');
                        return `${y}-${m}-${day}`;
                    }

                    function nextDayStr(dateStr) {
                        const d = new Date(dateStr);
                        if (isNaN(d)) return '';
                        d.setDate(d.getDate() + 1);
                        return fmt(d);
                    }

                    function validate() {
                        const a = ci.value,
                            b = co.value;
                        if (!a || !b) {
                            err.classList.add('hidden');
                            return true;
                        }
                        const da = new Date(a),
                            db = new Date(b);
                        if (isNaN(da) || isNaN(db)) {
                            err.classList.remove('hidden');
                            (isNaN(da) ? ci : co).focus();
                            return false;
                        }
                        const ok = db > da;
                        err.classList.toggle('hidden', ok);
                        if (!ok) {
                            try {
                                co.focus();
                            } catch (e) {}
                        }
                        return ok;
                    }

                    function syncMin() {
                        if (ci.value) {
                            const minCo = nextDayStr(ci.value);
                            if (minCo) {
                                co.min = minCo;
                                if (co.value && co.value < minCo) co.value = minCo;
                            }
                        }
                    }

                    function updateCtas() {
                        const base1 = cta1?.getAttribute('data-base') || '',
                            base2 = cta2?.getAttribute('data-base') || '';
                        const params = new URLSearchParams();
                        if (ci?.value) params.set('checkin', ci.value);
                        if (co?.value) params.set('checkout', co.value);
                        const qs = params.toString();
                        if (cta1 && base1) cta1.href = qs ? `${base1}?${qs}` : base1;
                        if (cta2 && base2) cta2.href = qs ? `${base2}?${qs}` : base2;
                    }

                    function applyStoredIfNoQuery() {
                        const urlParams = new URLSearchParams(window.location.search);
                        if (urlParams.has('checkin') || urlParams.has('checkout')) return;
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        let sIn = localStorage.getItem(kIn),
                            sOut = localStorage.getItem(kOut);
                        if (sIn) {
                            const dIn = new Date(sIn);
                            if (!isNaN(dIn) && dIn > today) ci.value = sIn;
                        }
                        if (sOut) {
                            const dOut = new Date(sOut),
                                dIn = new Date(ci.value);
                            if (!isNaN(dOut) && !isNaN(dIn) && dOut > dIn) co.value = sOut;
                        }
                    }
                    ci?.addEventListener('change', function() {
                        syncMin();
                        validate();
                        updateCtas();
                        if (ci?.value) localStorage.setItem(kIn, ci.value);
                    });
                    co?.addEventListener('change', function() {
                        validate();
                        updateCtas();
                        if (co?.value) localStorage.setItem(kOut, co.value);
                    });
                    form?.addEventListener('submit', function(ev) {
                        if (!validate()) ev.preventDefault();
                    });
                    applyStoredIfNoQuery();
                    syncMin();
                    validate();
                    updateCtas();
                })();
            </script>
        @endpush

        <div class="flex flex-wrap items-center justify-center gap-3 mt-6">
            <span
                class="inline-flex items-center px-3 py-1 text-xs sm:text-sm text-white bg-white rounded-full bg-opacity-20 backdrop-blur-sm"><i
                    class="mr-1 bi bi-wifi"></i> Wi‑Fi Gratis</span>
            <span
                class="inline-flex items-center px-3 py-1 text-xs sm:text-sm text-white bg-white rounded-full bg-opacity-20 backdrop-blur-sm"><i
                    class="mr-1 bi bi-shield-check"></i> Bebas Biaya Batal*</span>
            <span
                class="inline-flex items-center px-3 py-1 text-xs sm:text-sm text-white bg-white rounded-full bg-opacity-20 backdrop-blur-sm"><i
                    class="mr-1 bi bi-clock"></i> Check‑in 24/7</span>
        </div>
    </div>
</header>
