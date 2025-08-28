<div>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <select class="form-select" wire:model.live="status">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-auto">
                    <input type="date" class="form-control" wire:model.live="startDate">
                </div>
                <div class="col-auto">
                    <input type="date" class="form-control" wire:model.live="endDate">
                </div>
                <div class="col-auto">
                    <select class="form-select" wire:model.live="perPage">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <div class="col">
                    <input type="search" class="form-control" placeholder="Cari metode/notes..." wire:model.live.debounce.400ms="search">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('admin.payments.export', ['status' => $status, 'search' => $search, 'start' => $startDate, 'end' => $endDate]) }}" class="btn btn-sm btn-outline-secondary">Export CSV</a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Guest</th>
                            <th>Total</th>
                            <th>Bukti</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $idx => $bill)
                            <tr>
                                <td>{{ $items->firstItem() + $idx }}</td>
                                <td>
                                    {{ $bill->reservation->guest->full_name ?? $bill->reservation->guest->username }}<br>
                                    <small class="text-muted">{{ $bill->reservation->guest->email }}</small>
                                </td>
                                <td>Rp {{ number_format($bill->total_amount, 0, ',', '.') }}</td>
                                <td>
                                    @php($proof = $bill->payment_proof_path)
                                    @if($proof && Storage::disk('public')->exists($proof))
                                        <a href="{{ Storage::url($proof) }}" target="_blank">Lihat</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light">{{ $bill->payment_review_status ?? 'pending' }}</span>
                                </td>
                                <td>
                                    <div class="d-inline-flex gap-2">
                                        <button class="btn btn-success btn-sm" wire:click="approve({{ $bill->id }})">Approve</button>
                                        <button class="btn btn-outline-danger btn-sm" wire:click="reject({{ $bill->id }})">Reject</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $items->links() }}
        </div>
    </div>
</div>
