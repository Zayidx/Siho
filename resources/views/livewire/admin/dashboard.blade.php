<div>
    <section class="row">
        {{-- Kolom utama (kiri) --}}
        <div class="col-12 col-lg-9">
            {{-- Baris kartu statistik --}}
            <div class="row">
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                    <div class="stats-icon purple mb-2">
                                        <i class="iconly-boldHome"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Kamar Tersedia</h6>
                                    <h6 class="font-extrabold mb-0">{{ $availableRooms }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                    <div class="stats-icon blue mb-2">
                                        <i class="iconly-boldLogin"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Check-in Hari Ini</h6>
                                    <h6 class="font-extrabold mb-0">{{ $todaysCheckIns }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                    <div class="stats-icon green mb-2">
                                        <i class="iconly-boldWallet"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Pendapatan Bulan Ini</h6>
                                    <h6 class="font-extrabold mb-0">Rp{{ number_format($monthlyRevenue, 0, ',', '.') }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                    <div class="stats-icon red mb-2">
                                        <i class="iconly-boldLock"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Kamar Terisi</h6>
                                    <h6 class="font-extrabold mb-0">{{ $occupiedRooms }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Baris untuk chart utama --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Reservasi 7 Hari Terakhir</h4>
                        </div>
                        <div class="card-body">
                            <canvas id="weeklyReservationChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Baris untuk tabel tamu terbaru --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Tamu Terbaru</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-lg">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Email</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentGuests as $guest)
                                        <tr>
                                            <td class="col-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-md">
                                                        <img src="{{ $guest->foto_url ?: 'https://placehold.co/100x100/6c757d/white?text=' . strtoupper(substr($guest->full_name, 0, 1)) }}">
                                                    </div>
                                                    <p class="font-bold ms-3 mb-0">{{ $guest->full_name }}</p>
                                                </div>
                                            </td>
                                            <td class="col-auto">
                                                <p class=" mb-0">{{ $guest->email }}</p>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="2" class="text-center">Belum ada data tamu.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom sidebar (kanan) --}}
        <div class="col-12 col-lg-3">
            @auth
            <div class="card">
                <div class="card-body py-4 px-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-xl">
                            <img src="{{ auth()->user()->foto_url ?: 'https://placehold.co/100x100/6c757d/white?text=' . strtoupper(substr(auth()->user()->username, 0, 1)) }}" alt="Foto Profil">
                        </div>
                        <div class="ms-3 name">
                            <h5 class="font-bold">{{ auth()->user()->username }}</h5>
                            <h6 class="text-muted mb-0">{{ auth()->user()->role->name ?? 'No Role' }}</h6>
                        </div>
                    </div>
                </div>
            </div>
            @endauth
            <div class="card">
                <div class="card-header">
                    <h4>Status Kamar</h4>
                </div>
                <div class="card-body">
                    <canvas id="roomStatusChart"></canvas>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:navigated', () => {
        if (window.roomStatusChart instanceof Chart) {
            window.roomStatusChart.destroy();
        }
        if (window.weeklyReservationChart instanceof Chart) {
            window.weeklyReservationChart.destroy();
        }

        const roomStatusData = @json($roomStatusData);
        const weeklyReservationData = @json($weeklyReservationData);

        const roomStatusCtx = document.getElementById('roomStatusChart');
        if (roomStatusCtx) {
            window.roomStatusChart = new Chart(roomStatusCtx, {
                type: 'pie',
                data: {
                    labels: ['Tersedia', 'Terisi', 'Dibersihkan'],
                    datasets: [{
                        label: 'Jumlah Kamar',
                        data: [
                            roomStatusData.available,
                            roomStatusData.occupied,
                            roomStatusData.cleaning
                        ],
                        backgroundColor: [ '#435ebe', '#f3616d', '#ffc107' ],
                        hoverOffset: 4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        const weeklyReservationCtx = document.getElementById('weeklyReservationChart');
        if (weeklyReservationCtx) {
            const labels = [];
            const data = [];
            
            for (let i = 6; i >= 0; i--) {
                const d = new Date();
                d.setDate(d.getDate() - i);
                const dateString = d.toISOString().split('T')[0];
                labels.push(d.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric' }));
                data.push(weeklyReservationData[dateString] || 0);
            }

            window.weeklyReservationChart = new Chart(weeklyReservationCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Reservasi',
                        data: data,
                        backgroundColor: 'rgba(67, 94, 190, 0.8)',
                        borderColor: 'rgba(67, 94, 190, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });
        }
    });
</script>
@endpush
