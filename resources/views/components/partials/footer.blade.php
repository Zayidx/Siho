<!-- Footer -->
<footer class="text-white bg-gray-900">
    <div class="container px-4 py-12 mx-auto">
        <div class="grid gap-8 text-center md:grid-cols-2 lg:grid-cols-4 lg:text-left">
            <div class="mb-6 md:mb-0">
                <h5 class="mb-4 text-xl font-bold text-blue-500 uppercase">Grand Luxe</h5>
                <p class="text-gray-400">Pengalaman menginap mewah yang mendefinisikan kembali arti kenyamanan dan
                    keanggunan.</p>
            </div>
            <div class="mb-6 md:mb-0">
                <h5 class="mb-4 font-bold uppercase">Navigasi</h5>
                <ul class="space-y-2">
                    <li><a href="#alasan" class="text-gray-400 hover:text-white">Tentang</a></li>
                    <li><a href="#fasilitas" class="text-gray-400 hover:text-white">Fasilitas</a></li>
                    <li><a href="#harga" class="text-gray-400 hover:text-white">Harga</a></li>
                    <li><a href="#galeri" class="text-gray-400 hover:text-white">Galeri</a></li>
                    <li><a href="{{ route('menu') }}" class="text-gray-400 hover:text-white">Menu</a></li>
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
            <p class="text-sm text-gray-400">Hak Cipta ©{{ date('Y') }} <strong>Grand Luxe</strong>. Seluruh
                hak cipta dilindungi.</p>
            <div class="flex mt-4 space-x-6 sm:justify-center sm:mt-0">
                <a href="#" class="text-gray-400 hover:text-white" aria-label="Facebook"><i class="text-2xl bi bi-facebook"></i></a>
                <a href="#" class="text-gray-400 hover:text-white" aria-label="Twitter"><i class="text-2xl bi bi-twitter-x"></i></a>
                <a href="#" class="text-gray-400 hover:text-white" aria-label="Instagram"><i class="text-2xl bi bi-instagram"></i></a>
            </div>
        </div>
    </div>
</footer>

