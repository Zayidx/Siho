<div class="container py-4">
    @include('components.public.breadcrumbs', ['items' => [
        ['label' => 'Dashboard']
    ]])
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="m-0">Dashboard Pengguna</h2>
        <a href="{{ route('user.bills') }}" class="btn btn-outline-primary position-relative">
            Tagihan
            @if($unpaidBillsCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $unpaidBillsCount }}</span>
            @endif
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted">Tagihan Belum Dibayar</div>
                            <div class="display-6 fw-bold">{{ $unpaidBillsCount }}</div>
                        </div>
                        <i class="bi bi-receipt fs-1 text-primary"></i>
                    </div>
                    <div class="mt-3"><a href="{{ route('user.bills') }}" class="btn btn-sm btn-outline-primary">Lihat Tagihan</a></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted">Reservasi Mendatang</div>
                    @if ($upcoming)
                        <div class="mt-2">
                            <div class="fw-semibold">Check-in {{ $upcoming->check_in_date->format('Y-m-d') }}</div>
                            <div class="small text-muted">Kamar: {{ $upcoming->rooms->pluck('room_number')->join(', ') }}</div>
                            <a href="{{ route('user.reservations.show', ['reservation' => $upcoming->id]) }}" class="btn btn-sm btn-outline-secondary mt-3">Lihat Detail</a>
                        </div>
                    @else
                        <div class="mt-2 text-muted">Belum ada jadwal. Yuk pesan kamar.</div>
                        <a href="{{ route('booking.hotel') }}" class="btn btn-sm btn-primary mt-3">Pesan Kamar</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <div class="text-muted">Aksi Cepat</div>
                        <div class="mt-2 d-grid gap-2">
                            <a href="{{ route('booking.hotel') }}" class="btn btn-primary">Pesan Kamar</a>
                            <a href="{{ route('booking.hotel') }}" class="btn btn-outline-secondary">Lihat Semua Kamar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header"><strong>Reservasi Terbaru</strong></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Kamar</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentReservations as $r)
                                <tr>
                                    <td>{{ $r->id }}</td>
                                    <td>{{ $r->check_in_date->format('Y-m-d') }}</td>
                                    <td>{{ $r->check_out_date->format('Y-m-d') }}</td>
                                    <td>{{ $r->rooms->pluck('room_number')->join(', ') }}</td>
                                    <td><span class="badge text-bg-{{ $r->status === 'Confirmed' ? 'warning' : ($r->status === 'Checked-in' ? 'success' : ($r->status === 'Completed' ? 'secondary' : 'danger')) }}">{{ $r->status }}</span></td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('user.reservations.show', ['reservation' => $r->id]) }}">Detail</a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada reservasi.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header"><strong>Tagihan Terbaru</strong></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Reservasi</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Dibuat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentBills as $b)
                                <tr>
                                    <td>{{ $b->id }}</td>
                                    <td>#{{ $b->reservation_id }}</td>
                                    <td>Rp {{ number_format($b->total_amount) }}</td>
                                    <td>
                                        @if($b->paid_at)
                                            <span class="badge text-bg-success">Paid</span>
                                        @elseif($b->payment_review_status === 'pending')
                                            <span class="badge text-bg-warning">Pending</span>
                                        @elseif($b->payment_review_status === 'rejected')
                                            <span class="badge text-bg-danger">Rejected</span>
                                        @else
                                            <span class="badge text-bg-secondary">Unpaid</span>
                                        @endif
                                    </td>
                                    <td>{{ $b->created_at->format('Y-m-d') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada tagihan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
