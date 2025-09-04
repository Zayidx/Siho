<div class="container py-4">
    <h1 class="h4 mb-3">Pesanan Makanan Saya</h1>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead><tr><th>#</th><th>Tanggal</th><th>Layanan</th><th>Rincian</th><th>Total</th><th>Status</th><th>Pembayaran</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($orders as $o)
                        <tr>
                            <td>{{ $o->id }}</td>
                            <td>{{ $o->created_at->format('d M Y H:i') }}</td>
                            <td>{{ ucfirst(str_replace('_',' ', $o->service_type ?? 'in_room')) }}</td>
                            <td>
                                <ul class="m-0 small">
                                    @foreach($o->items as $it)
                                        <li>{{ $it->qty }}x {{ $it->item?->name }} <span class="text-muted">(Rp{{ number_format($it->line_total,0,',','.') }})</span></li>
                                    @endforeach
                                </ul>
                                @if($o->notes)
                                    <div class="text-muted small">Catatan: {{ $o->notes }}</div>
                                @endif
                            </td>
                            <td class="fw-bold">Rp{{ number_format($o->total_amount,0,',','.') }}</td>
                            <td>{{ ucfirst($o->status) }}</td>
                            <td>{{ ucfirst($o->payment_status) }}</td>
                            <td>
                                @if($o->status === 'pending')
                                    <button class="btn btn-sm btn-outline-danger" wire:click="cancel({{ $o->id }})">Batalkan</button>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4">Belum ada pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $orders->links() }}</div>
    </div>
</div>

