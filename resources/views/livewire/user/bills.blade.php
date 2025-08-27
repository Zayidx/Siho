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
                                </div>
                                <div class="mt-2">
                                    <div class="small text-muted mb-1">Instruksi Pembayaran (Transfer Bank)</div>
                                    <div class="small text-muted">
                                        Bank: <strong>{{ config('payment.bank.name') }}</strong> · No.Rek: <strong>{{ implode(' ', str_split(config('payment.bank.account'), 4)) }}</strong> · A/N: <strong>{{ config('payment.bank.holder') }}</strong>
                                    </div>
                                    <div class="small text-muted">{{ config('payment.bank.note') }}</div>
                                    @if(!$bill->payment_proof_path || $bill->payment_review_status==='rejected')
                                        <label class="form-label small mb-1 mt-2">Upload Bukti Pembayaran</label>
                                        <div class="d-flex gap-2">
                                            <input type="file" class="form-control form-control-sm" wire:model="proofFile" accept=".jpg,.jpeg,.png,.pdf">
                                            <button class="btn btn-sm btn-outline-primary" wire:click="uploadProof({{ $bill->id }})" wire:loading.attr="disabled">Kirim</button>
                                        </div>
                                        @error('proofFile')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                                    @else
                                        <div class="small text-muted mt-2">Bukti sudah diunggah: <a href="#" wire:click.prevent="openPreviewBill({{ $bill->id }})">Lihat</a> · Status: <strong>{{ strtoupper($bill->payment_review_status ?? 'pending') }}</strong></div>
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
                                <ul class="list-unstyled mb-0" style="max-height: 140px; overflow:auto;">
                                    @foreach($bill->logs->take(6) as $log)
                                        <li class="d-flex justify-content-between align-items-start">
                                            <span>
                                                <span class="badge bg-light text-dark">{{ $log->label }}</span>
                                                @if($log->action==='proof_uploaded')
                                                    · <a href="#" wire:click.prevent="openPreviewBill({{ $bill->id }})">Lihat bukti</a>
                                                @endif
                                            </span>
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

    @if($previewUrl)
        <div class="position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.85); z-index: 1080;">
            <button class="btn btn-light position-absolute" style="top:16px; right:16px;" wire:click="closePreview">Tutup</button>
            <div class="d-flex align-items-center justify-content-center h-100 p-3">
                <iframe src="{{ $previewUrl }}" style="width: 90%; height: 90%; background:#fff; border-radius:6px;" frameborder="0"></iframe>
            </div>
        </div>
    @endif

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
                                @if(!$selectedBill->payment_proof_path || $selectedBill->payment_review_status==='rejected')
                                    <strong>Upload Bukti Pembayaran (Transfer):</strong>
                                    <div class="mt-2 d-flex align-items-center gap-2">
                                        <input type="file" class="form-control" wire:model="proofFile" accept=".jpg,.jpeg,.png,.pdf">
                                        <button class="btn btn-outline-primary" wire:click="uploadProof({{ $selectedBill->id }})" wire:loading.attr="disabled">
                                            <span wire:loading.remove>Unggah</span>
                                            <span wire:loading class="spinner-border spinner-border-sm"></span>
                                        </button>
                                    </div>
                                    @error('proofFile')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                                @else
                                    <div class="alert alert-info mt-2">
                                        Bukti pembayaran sudah diunggah pada {{ optional($selectedBill->payment_proof_uploaded_at)->format('Y-m-d H:i') }}.
                                        <a href="{{ Storage::url($selectedBill->payment_proof_path) }}" target="_blank">Lihat bukti</a>
                                        · Status: <strong>{{ strtoupper($selectedBill->payment_review_status ?? 'pending') }}</strong>
                                    </div>
                                @endif
                                <div class="mt-2 small text-muted">
                                    Transfer ke <strong>{{ config('payment.bank.name') }}</strong> a.n <strong>{{ config('payment.bank.holder') }}</strong> no. rek <strong>{{ implode(' ', str_split(config('payment.bank.account'), 4)) }}</strong>.
                                    Sertakan kode invoice: <strong>#{{ $selectedBill->id }}</strong>.
                                </div>
                            </div>
                        @else
                            <div class="mt-3">
                                <a href="{{ route('user.bills.invoice', ['bill' => $selectedBill->id]) }}" class="btn btn-outline-secondary" target="_blank">Unduh Invoice (PDF)</a>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" wire:click="closeDetail">Tutup</button>
                        
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
