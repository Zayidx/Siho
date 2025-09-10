<div>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <select class="form-select" wire:model.live="status">
                        <option value="">Semua</option>
                        <option value="unread">Belum dibaca</option>
                        <option value="read">Sudah dibaca</option>
                    </select>
                </div>
                <div class="col-auto">
                    <input type="date" class="form-control" wire:model.live="startDate" placeholder="Mulai">
                </div>
                <div class="col-auto">
                    <input type="date" class="form-control" wire:model.live="endDate" placeholder="Selesai">
                </div>
                <div class="col-auto">
                    <select class="form-select" wire:model.live="perPage">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <div class="col">
                    <input type="search" class="form-control" placeholder="Cari nama/email/pesan..."
                        wire:model.live.debounce.400ms="search">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('admin.contacts.export', ['status' => $status, 'search' => $search, 'start' => $startDate, 'end' => $endDate]) }}"
                    class="btn btn-sm btn-outline-secondary">Export CSV</a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Pesan</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $idx => $m)
                            <tr>
                                <td>{{ $items->firstItem() + $idx }}</td>
                                <td>{{ $m->name }}</td>
                                <td>{{ $m->email }}</td>
                                <td class="text-truncate" style="max-width: 360px;" title="{{ $m->message }}">
                                    {{ $m->message }}</td>
                                <td>{{ $m->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    @if ($m->read_at)
                                        <span class="badge bg-success">Dibaca</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Baru</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary"
                                            wire:click="view({{ $m->id }})">Lihat</button>
                                        @if (!$m->read_at)
                                            <button class="btn btn-sm btn-outline-success"
                                                wire:click="markRead({{ $m->id }})">Tandai Dibaca</button>
                                        @endif
                                        <button class="btn btn-sm btn-outline-danger"
                                            wire:click="delete({{ $m->id }})">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Tidak ada pesan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $items->links() }}
        </div>
    </div>

    <div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $selected ? 'Pesan dari ' . $selected->name : 'Pesan' }}</h5>
                    <button type="button" class="btn-close" wire:click="close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if ($selected)
                        <div class="mb-2"><strong>Email:</strong> {{ $selected->email }}</div>
                        <div class="mb-2"><strong>Tanggal:</strong> {{ $selected->created_at->format('Y-m-d H:i') }}
                        </div>
                        <div class="mb-2"><strong>IP:</strong> {{ $selected->ip ?? '-' }}</div>
                        <hr>
                        <p class="mb-0" style="white-space: pre-wrap;">{{ $selected->message }}</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="close" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>
