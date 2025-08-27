<div class="container py-4">
    @include('components.public.breadcrumbs', ['items' => [
        ['label' => 'Dashboard', 'url' => route('user.dashboard')],
        ['label' => 'Tagihan']
    ]])
    <h2 class="mb-4">Tagihan Saya</h2>

    <div class="card mb-4">
        <div class="card-body">
            <ul class="nav nav-pills mb-2">
                <li class="nav-item"><a class="nav-link {{ $status===''?'active':'' }}" href="#" wire:click.prevent="$set('status','')">Semua</a></li>
                <li class="nav-item"><a class="nav-link {{ $status==='unpaid'?'active':'' }}" href="#" wire:click.prevent="$set('status','unpaid')">Unpaid</a></li>
                <li class="nav-item"><a class="nav-link {{ $status==='pending'?'active':'' }}" href="#" wire:click.prevent="$set('status','pending')">Pending</a></li>
                <li class="nav-item"><a class="nav-link {{ $status==='rejected'?'active':'' }}" href="#" wire:click.prevent="$set('status','rejected')">Rejected</a></li>
                <li class="nav-item"><a class="nav-link {{ $status==='paid'?'active':'' }}" href="#" wire:click.prevent="$set('status','paid')">Paid</a></li>
            </ul>
            <div class="row g-2 align-items-center">
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

    <div class="row g-3">
        @forelse ($bills as $idx => $bill)
            @php
                $isPaid = (bool)$bill->paid_at;
                $status = $isPaid ? 'Paid' : ($bill->payment_review_status ?: 'Unpaid');
                $badgeClass = $isPaid ? 'bg-success' : ($status === 'pending' ? 'bg-warning text-dark' : ($status === 'rejected' ? 'bg-danger' : 'bg-secondary'));
            @endphp
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="text-muted small">Invoice #{{ $bill->id }}</div>
                                <div class="h5 m-0">Rp {{ number_format($bill->total_amount, 0, ',', '.') }}</div>
                                <div class="text-muted small">Terbit: {{ optional($bill->issued_at)->format('Y-m-d H:i') }}</div>
                                <div class="text-muted small">Metode: {{ $bill->payment_method ?? '-' }}</div>
                            </div>
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                        </div>

                        @if(!$isPaid)
                            <div class="mt-3">
                                <div class="text-muted small mb-1">Aksi</div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-sm btn-outline-primary" wire:click="view({{ $bill->id }})">Detail</button>
                                    <button class="btn btn-sm btn-warning" wire:click="pay({{ $bill->id }}, 'Manual')">Ajukan Verifikasi</button>
                                    <button class="btn btn-sm btn-primary" wire:click="pay({{ $bill->id }}, 'Online')">Bayar Online (Mock)</button>
                                </div>
                                <div class="mt-2">
                                    <label class="form-label small mb-1">Upload Bukti Pembayaran (opsional)</label>
                                    <div class="d-flex gap-2">
                                        <input type="file" class="form-control form-control-sm" wire:model="proofFile">
                                        <button class="btn btn-sm btn-outline-primary" wire:click="uploadProof({{ $bill->id }})" wire:loading.attr="disabled">Kirim</button>
                                    </div>
                                    @if($bill->payment_proof_path)
                                        <div class="small text-muted mt-1">Bukti: <a target="_blank" href="{{ Storage::url($bill->payment_proof_path) }}">Lihat</a> (status: {{ $bill->payment_review_status ?? 'pending' }})</div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="mt-3">
                                <a href="{{ route('user.bills.invoice', ['bill' => $bill->id]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Unduh Invoice (PDF)</a>
                            </div>
                        @endif

                        @if($bill->logs && $bill->logs->count())
                            <div class="mt-3 small">
                                <div class="text-muted mb-1">Riwayat</div>
                                <ul class="list-unstyled mb-0" style="max-height: 120px; overflow:auto;">
                                    @foreach($bill->logs->take(5) as $log)
                                        <li>
                                            <span class="badge bg-light text-dark">{{ $log->action }}</span>
                                            <span class="text-muted">{{ $log->created_at->format('Y-m-d H:i') }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mt-auto d-flex justify-content-end pt-2">
                            <span class="text-muted small">#{{ $bills->firstItem() + $idx }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-warning">Tidak ada tagihan.</div></div>
        @endforelse
    </div>
    <div class="mt-3">{{ $bills->links() }}</div>

    @if($showDetail && $selectedBill)
        <div class="modal fade show" style="display:block;" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Tagihan #{{ $selectedBill->id }}</h5>
                        <button type="button" class="btn-close" wire:click="closeDetail"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div><strong>Status:</strong>
                                    @if($selectedBill->paid_at)
                                        <span class="badge bg-success">Paid</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Unpaid</span>
                                    @endif
                                </div>
                                <div><strong>Total:</strong> Rp {{ number_format($selectedBill->total_amount, 0, ',', '.') }}</div>
                                <div><strong>Diterbitkan:</strong> {{ optional($selectedBill->issued_at)->format('Y-m-d H:i') }}</div>
                                <div><strong>Dibayar:</strong> {{ optional($selectedBill->paid_at)->format('Y-m-d H:i') ?? '-' }}</div>
                                <div><strong>Metode:</strong> {{ $selectedBill->payment_method ?? '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div><strong>Reservasi:</strong> #{{ $selectedBill->reservation->id }}</div>
                                <div><strong>Check-in:</strong> {{ $selectedBill->reservation->check_in_date->format('Y-m-d') }}</div>
                                <div><strong>Check-out:</strong> {{ $selectedBill->reservation->check_out_date->format('Y-m-d') }}</div>
                                <div><strong>Kamar:</strong>
                                    @if($selectedBill->reservation->rooms->count())
                                        {{ $selectedBill->reservation->rooms->pluck('room_number')->join(', ') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div>
                            <strong>Catatan:</strong>
                            <div class="text-muted">{{ $selectedBill->notes ?? '-' }}</div>
                        </div>
                        @if(!$selectedBill->paid_at)
                            <div class="mt-3">
                                <strong>Upload Bukti Pembayaran (Transfer):</strong>
                                <div class="mt-2 d-flex align-items-center gap-2">
                                    <input type="file" class="form-control" wire:model="proofFile">
                                    <button class="btn btn-outline-primary" wire:click="uploadProof({{ $selectedBill->id }})" wire:loading.attr="disabled">
                                        <span wire:loading.remove>Unggah</span>
                                        <span wire:loading class="spinner-border spinner-border-sm"></span>
                                    </button>
                                </div>
                                @if($selectedBill->payment_proof_path)
                                    <div class="small text-muted mt-2">
                                        Bukti terkini: <a href="{{ Storage::url($selectedBill->payment_proof_path) }}" target="_blank">Lihat</a>
                                        (status: {{ $selectedBill->payment_review_status ?? 'pending' }})
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="mt-3">
                                <a href="{{ route('user.bills.invoice', ['bill' => $selectedBill->id]) }}" class="btn btn-outline-secondary" target="_blank">Unduh Invoice (PDF)</a>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" wire:click="closeDetail">Tutup</button>
                        @if(!$selectedBill->paid_at)
                            <button class="btn btn-primary" wire:click="pay({{ $selectedBill->id }}, 'Manual')">Bayar (Mock)</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
