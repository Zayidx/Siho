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
                                <label class="form-label">Upload Bukti Pembayaran</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="file" class="form-control" wire:model="proofFile">
                                    <button class="btn btn-primary btn-sm" wire:click="uploadProof" wire:loading.attr="disabled">Kirim</button>
                                </div>
                                @error('proofFile') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @if($reservation->bill->payment_proof_path)
                                    <div class="small text-muted mt-2">Bukti saat ini: <a target="_blank" href="{{ Storage::url($reservation->bill->payment_proof_path) }}">Lihat</a> (status: {{ $reservation->bill->payment_review_status ?? 'pending' }})</div>
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
                                <strong>{{ $log->action }}</strong>
                                @if($log->meta)
                                    <span class="text-muted">{{ json_encode($log->meta) }}</span>
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
</div>
