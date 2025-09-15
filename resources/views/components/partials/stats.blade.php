<!-- Stats Band -->
<section class="relative z-20 -mt-12">
    <div class="container px-4 mx-auto">
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div
                class="flex items-center p-6 space-x-4 transition duration-300 transform bg-white rounded-lg shadow-lg dark:bg-gray-800 hover:-translate-y-2">
                <i class="text-4xl text-blue-600 bi bi-emoji-smile"></i>
                <div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($stats['guestCount'] ?? 0) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Reservasi</div>
                </div>
            </div>
            <div
                class="flex items-center p-6 space-x-4 transition duration-300 transform bg-white rounded-lg shadow-lg dark:bg-gray-800 hover:-translate-y-2">
                <i class="text-4xl text-blue-600 bi bi-star-fill"></i>
                <div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">4.8/5</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Ulasan Rata-rata</div>
                </div>
            </div>
            <div
                class="flex items-center p-6 space-x-4 transition duration-300 transform bg-white rounded-lg shadow-lg dark:bg-gray-800 hover:-translate-y-2">
                <i class="text-4xl text-blue-600 bi bi-buildings"></i>
                <div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($stats['roomCount'] ?? 0) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Kamar</div>
                </div>
            </div>
            <div
                class="flex items-center p-6 space-x-4 transition duration-300 transform bg-white rounded-lg shadow-lg dark:bg-gray-800 hover:-translate-y-2">
                <i class="text-4xl text-blue-600 bi bi-award"></i>
                <div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">Top 10</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Hotel di Jakarta</div>
                </div>
            </div>
        </div>
    </div>
</section>
