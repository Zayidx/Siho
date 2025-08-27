<div class="container py-4">
    @include('components.public.breadcrumbs', ['items' => [
        ['label' => 'Dashboard', 'url' => route('user.dashboard')],
        ['label' => 'Reservasi Saya', 'url' => route('user.reservations')],
        ['label' => 'Detail']
    ]])
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Detail Reservasi #{{ $reservation->id }}</h5>
            <div class="row" wire:poll.10s>
                <div class="col-md-6">
                    <ul class="list-unstyled mb-0">
                        <li><strong>Check-in:</strong> {{ $reservation->check_in_date->format('Y-m-d') }}</li>
                        <li><strong>Check-out:</strong> {{ $reservation->check_out_date->format('Y-m-d') }}</li>
                        <li><strong>Status:</strong> {{ $reservation->status }}</li>
                        <li><strong>Kamar:</strong> {{ $reservation->rooms->pluck('room_number')->join(', ') ?: '-' }}</li>
                    </ul>
                    @if(isset($typeSummary) && count($typeSummary))
                        <div class="mt-3">
                            <h6>Detail Tipe Kamar</h6>
                            <div class="list-group">
                                @foreach($typeSummary as $t)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-semibold">{{ $t['name'] }}</div>
                                                <div class="small text-muted">Jumlah: {{ $t['count'] }} kamar @if($t['capacity'])· Kapasitas: {{ $t['capacity'] }} org/kamar @endif</div>
                                            </div>
                                            <div class="text-end">
                                                <div class="small text-muted">Rata-rata/malam</div>
                                                <div class="fw-semibold">Rp {{ number_format($t['avg_price'], 0, ',', '.') }}</div>
                                            </div>
                                        </div>
                                        @if(!empty($t['facilities']))
                                            <div class="mt-2 d-flex flex-wrap gap-2">
                                                @foreach($t['facilities'] as $f)
                                                    <span class="badge bg-light text-dark border"><i class="{{ $f['icon'] }}"></i> {{ $f['name'] }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <h6>Tagihan</h6>
                    @if($reservation->bill)
                        <div>Total: <strong>Rp {{ number_format($reservation->bill->total_amount, 0, ',', '.') }}</strong></div>
                        <div>Status: {{ $reservation->bill->paid_at ? 'Paid' : 'Unpaid' }}</div>
                        @if($reservation->bill->paid_at)
                            <div class="mt-2">
                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('user.bills.invoice', ['bill' => $reservation->bill->id]) }}" target="_blank">Unduh Invoice (PDF)</a>
                            </div>
                        @else
                            <div class="mt-3">
                                @if(!$reservation->bill->payment_proof_path || $reservation->bill->payment_review_status==='rejected')
                                    <label class="form-label">Upload Bukti Pembayaran</label>
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="file" class="form-control" wire:model="proofFile" accept=".jpg,.jpeg,.png,.pdf">
                                        <button class="btn btn-primary btn-sm" wire:click="uploadProof" wire:loading.attr="disabled">Kirim</button>
                                    </div>
                                    @error('proofFile') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @else
                                    <div class="alert alert-info">
                                        Bukti sudah diunggah pada {{ optional($reservation->bill->payment_proof_uploaded_at)->format('Y-m-d H:i') }}. 
                                        <a href="#" wire:click.prevent="openPreview">Lihat bukti</a> · Status: <strong>{{ strtoupper($reservation->bill->payment_review_status ?? 'pending') }}</strong>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="text-muted">Belum ada tagihan.</div>
                    @endif
                </div>
            </div>
            @if($reservation->bill)
            <hr>
            <h6>Riwayat Pembayaran</h6>
            @if($reservation->bill->logs && $reservation->bill->logs->count())
                <ul class="list-group">
                    @foreach($reservation->bill->logs as $log)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-light text-dark">{{ $log->label }}</span>
                                @if($log->action==='proof_uploaded' && !empty($log->meta['path']))
                                    · <a href="#" wire:click.prevent="openPreview">Lihat bukti</a>
                                @endif
                            </div>
                            <span class="text-muted small">{{ $log->created_at->format('Y-m-d H:i') }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="text-muted">Belum ada riwayat.</div>
            @endif
            @endif
        </div>
    </div>

    @if($previewUrl)
        <div class="position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.85); z-index: 1080;">
            <button class="btn btn-light position-absolute" style="top:16px; right:16px;" wire:click="closePreview">Tutup</button>
            <div class="d-flex align-items-center justify-content-center h-100 p-3">
                <iframe src="{{ $previewUrl }}" style="width: 90%; height: 90%; background:#fff; border-radius:6px;" frameborder="0"></iframe>
            </div>
        </div>
    @endif
</div>
