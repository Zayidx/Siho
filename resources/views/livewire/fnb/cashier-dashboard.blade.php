<div class="container py-4">
    <h1 class="h3 mb-3">Dashboard Kasir F&amp;B</h1>
    <div class="card mb-3">
        <div class="card-body d-flex align-items-center gap-2">
            <label class="me-2">Filter Status</label>
            <select class="form-select" style="width:auto" wire:model.live="statusFilter">
                <option value="">Semua</option>
                <option value="pending">Pending</option>
                <option value="preparing">Preparing</option>
                <option value="ready">Ready</option>
                <option value="served">Served</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>

    <div class="card" wire:poll.5s>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pemesan</th>
                        <th>Rincian</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Layanan</th>
                        <th>Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $o)
                        <tr>
                            <td>{{ $o->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $o->user?->full_name ?: $o->user?->username }}</div>
                                <div class="text-muted small">Kamar: {{ $o->room_number ?: '-' }}</div>
                            </td>
                            <td>
                                <ul class="m-0 small">
                                    @foreach ($o->items as $it)
                                        <li>{{ $it->qty }}x {{ $it->item?->name }} <span
                                                class="text-muted">(Rp{{ number_format($it->line_total, 0, ',', '.') }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                                @if ($o->notes)
                                    <div class="mt-1 text-muted small">Catatan: {{ $o->notes }}</div>
                                @endif
                            </td>
                            <td class="fw-bold">Rp{{ number_format($o->total_amount, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-light">{{ ucfirst($o->status) }}</span>
                            </td>
                            <td>{{ ucfirst(str_replace('_', ' ', $o->service_type ?? 'in_room')) }}</td>
                            <td>
                                <span
                                    class="badge {{ $o->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($o->payment_status) }}</span>
                            </td>
                            <td class="d-flex gap-1">
                                <div class="btn-group">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                        data-bs-toggle="dropdown">Ubah Status</button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#"
                                                wire:click.prevent="setStatus({{ $o->id }}, 'pending')">Pending</a>
                                        </li>
                                        <li><a class="dropdown-item" href="#"
                                                wire:click.prevent="setStatus({{ $o->id }}, 'preparing')">Preparing</a>
                                        </li>
                                        <li><a class="dropdown-item" href="#"
                                                wire:click.prevent="setStatus({{ $o->id }}, 'ready')">Ready</a>
                                        </li>
                                        <li><a class="dropdown-item" href="#"
                                                wire:click.prevent="setStatus({{ $o->id }}, 'served')">Served</a>
                                        </li>
                                        <li><a class="dropdown-item text-danger" href="#"
                                                wire:click.prevent="setStatus({{ $o->id }}, 'cancelled')">Cancel</a>
                                        </li>
                                    </ul>
                                </div>
                                @if ($o->payment_status !== 'paid')
                                    <button class="btn btn-success btn-sm"
                                        wire:click="markPaid({{ $o->id }})">Tandai Lunas</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-3">Belum ada pesanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">
            {{ $orders->links() }}
        </div>
    </div>
</div>
