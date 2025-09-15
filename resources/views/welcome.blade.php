<x-layouts.public>

    @push('styles')
        <style>
            body { font-family: 'Roboto', sans-serif; }
            h1, h2, h3, h4, h5, h6 { font-family: 'Playfair Display', serif; }
            input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(var(--tw-dark-mode-invert, 0)); }
            html.dark { --tw-dark-mode-invert: 1; }
        </style>
    @endpush

    @include('components.partials.hero')

    @include('components.partials.stats')

    <main class="text-gray-800 bg-gray-50 dark:bg-gray-950 dark:text-gray-200">
        @include('components.partials.reasons')
        @include('components.partials.pricing')
        @include('components.partials.popular-menu')
        @include('components.partials.gallery')
        @include('components.partials.facilities')
        @include('components.partials.faq')
        @include('components.partials.contact')
    </main>

    @include('components.partials.footer')

    @push('scripts')
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
                if (facilityCarousel) {
                    const slides = Array.from(facilityCarousel.children);
                    let currentSlide = 0;
                    setInterval(() => {
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

                    if (slides.length) {
                        function thumbSafe(fn) {
                            if (!thumbnails || !thumbnails.length) return;
                            fn();
                        }

                        function showSlide(index) {
                            slides[currentIndex]?.classList.add('hidden');
                            thumbSafe(() => {
                                thumbnails[currentIndex]?.classList.add('opacity-60');
                                thumbnails[currentIndex]?.classList.remove('opacity-100', 'border-2', 'border-blue-600');
                            });

                            currentIndex = (index + slides.length) % slides.length;

                            slides[currentIndex]?.classList.remove('hidden');
                            thumbSafe(() => {
                                thumbnails[currentIndex]?.classList.remove('opacity-60');
                                thumbnails[currentIndex]?.classList.add('opacity-100', 'border-2', 'border-blue-600');
                            });
                        }

                        if (prevButton) prevButton.addEventListener('click', () => showSlide(currentIndex - 1));
                        if (nextButton) nextButton.addEventListener('click', () => showSlide(currentIndex + 1));
                        if (thumbnails && thumbnails.length) {
                            thumbnails.forEach((thumb, index) => { thumb.addEventListener('click', () => showSlide(index)); });
                        }

                        showSlide(0);
                        setInterval(() => showSlide(currentIndex + 1), 4000);
                    }
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

                // Fullscreen overlay for images with .fs-img
                const overlay = document.createElement('div');
                overlay.id = 'fsOverlay';
                overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.9);display:none;align-items:center;justify-content:center;z-index:9999;padding:16px;';
                overlay.innerHTML = '<button id="fsClose" style="position:absolute;top:16px;right:16px;background:#fff;color:#000;border:none;border-radius:6px;padding:8px 12px;cursor:pointer">Tutup</button><img id="fsImage" src="" style="max-width:100%;max-height:100%;object-fit:contain;" />';
                document.body.appendChild(overlay);
                const fsImg = overlay.querySelector('#fsImage');
                const fsClose = overlay.querySelector('#fsClose');
                fsClose.addEventListener('click', () => { overlay.style.display = 'none'; document.exitFullscreen?.(); });
                overlay.addEventListener('click', (e) => { if (e.target === overlay) { overlay.style.display = 'none'; document.exitFullscreen?.(); } });
                document.querySelectorAll('.fs-img').forEach(img => { img.addEventListener('click', () => { fsImg.src = img.src; overlay.style.display = 'flex'; overlay.requestFullscreen?.(); }); });
            });
        </script>
    @endpush

</x-layouts.public>
