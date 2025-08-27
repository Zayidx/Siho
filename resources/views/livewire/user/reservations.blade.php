<div class="container py-4">
    @include('components.public.breadcrumbs', ['items' => [
        ['label' => 'Dashboard', 'url' => route('user.dashboard')],
        ['label' => 'Reservasi Saya']
    ]])
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-2">
                    <select class="form-select" style="width:auto;" wire:model.live="perPage">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                    <input type="search" class="form-control" placeholder="Cari status/catatan..." wire:model.live.debounce.400ms="search">
                </div>
                <a class="btn btn-primary" href="{{ route('booking') }}">Pesan Kamar</a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Status</th>
                            <th>Kamar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $idx => $r)
                            <tr>
                                <td>{{ $items->firstItem() + $idx }}</td>
                                <td>{{ $r->check_in_date->format('Y-m-d') }}</td>
                                <td>{{ $r->check_out_date->format('Y-m-d') }}</td>
                                <td>{{ $r->status }}</td>
                                <td>{{ $r->rooms->pluck('room_number')->join(', ') ?: '-' }}</td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('user.reservations.show', ['reservation' => $r->id]) }}">Lihat</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Belum ada reservasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $items->links() }}
        </div>
    </div>
</div>
