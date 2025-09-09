<!-- Contact Section -->
<section id="kontak" class="py-20 bg-gray-100 dark:bg-gray-950 fade-in-section">
    <div class="container px-4 mx-auto">
        <div class="mb-12 text-center">
            <h2 class="text-4xl font-bold text-gray-900 dark:text-white">Hubungi Kami</h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Kami siap membantu Anda kapan pun.</p>
        </div>
        <div class="grid grid-cols-1 gap-8 md:grid-cols-5">
            <!-- Info Side (2/5 width) -->
            <div class="col-span-2 p-8 text-white bg-blue-900 rounded-lg md:p-12">
                <h3 class="text-3xl font-bold">Informasi Kontak</h3>
                <p class="mt-2 text-blue-100">Silakan hubungi kami melalui kontak berikut untuk pertanyaan, reservasi, atau kerja sama.</p>
                <div class="mt-8 space-y-6">
                    <div class="flex items-start">
                        <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 text-2xl text-white bg-white rounded-full bg-opacity-20">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold">Alamat</h4>
                            <p class="text-blue-100">Jl. Kemewahan No. 1, Jakarta Pusat, Indonesia</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 text-2xl text-white bg-white rounded-full bg-opacity-20">
                            <i class="bi bi-telephone-fill"></i>
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold">Telepon</h4>
                            <p class="text-blue-100">(021) 1234-5678</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 text-2xl text-white bg-white rounded-full bg-opacity-20">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold">Email</h4>
                            <p class="text-blue-100">{{ $contactEmail ?? 'info@grandluxe.com' }}</p>
                        </div>
                    </div>
                </div>
                <div class="pt-8 mt-12 border-t border-white/20">
                    <h4 class="font-semibold">Jam Operasional</h4>
                    <p class="mt-1 text-blue-100">Layanan Tamu: 24 Jam Non-Stop</p>
                    <p class="mt-1 text-blue-100">Reservasi: 08:00 - 22:00 WIB</p>
                </div>
            </div>
            <!-- Form Side (3/5 width) -->
            <div class="col-span-3 p-8 bg-white rounded-lg dark:bg-gray-800 md:p-12">
                <h3 class="text-3xl font-bold text-gray-900 dark:text-white">Kirim Pesan</h3>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Punya pertanyaan? Isi formulir di bawah dan tim kami akan segera merespons Anda.</p>
                <div class="mt-8">
                    @livewire('public.contact-form')
                </div>
            </div>
        </div>
    </div>
</section>

