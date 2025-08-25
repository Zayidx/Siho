<div class="container py-4">
    <h2 class="mb-4">Dashboard Pengguna</h2>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Buat Reservasi Baru</strong>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="createReservation">
                        <div class="mb-3">
                            <label class="form-label">Tanggal Check-in</label>
                            <input type="date" class="form-control @error('check_in_date') is-invalid @enderror" wire:model.defer="check_in_date">
                            @error('check_in_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Check-out</label>
                            <input type="date" class="form-control @error('check_out_date') is-invalid @enderror" wire:model.defer="check_out_date">
                            @error('check_out_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label d-block">Pilih Kamar per Tipe</label>
                            @if (empty($availableRoomTypes))
                                <div class="text-muted">Tidak ada kamar tersedia saat ini.</div>
                            @else
                                @foreach ($availableRoomTypes as $type => $info)
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <div class="flex-grow-1">
                                            <strong>{{ $type }}</strong>
                                            <span class="text-muted">(tersedia: {{ $info['available_count'] }})</span>
                                            <div class="small text-muted">Rp {{ number_format($info['avg_price']) }} / malam</div>
                                        </div>
                                        <div class="btn-group" role="group" aria-label="counter">
                                            <button type="button" class="btn btn-outline-secondary" wire:click="decrementRoomType('{{ $type }}')">-</button>
                                            <button type="button" class="btn btn-light" disabled style="min-width:60px;">
                                                {{ $selectedRoomTypes[$type] ?? 0 }}
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" wire:click="incrementRoomType('{{ $type }}')">+</button>
                                        </div>
                                    </div>
                                @endforeach
                                @error('selectedRoomTypes') <div class="text-danger small">{{ $message }}</div> @enderror
                            @endif
                        </div>
                        <div class="mb-3 p-3 bg-light rounded border">
                            <div class="d-flex justify-content-between">
                                <div><strong>Lama Menginap</strong></div>
                                <div><strong>{{ $this->nights }}</strong> malam</div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div><strong>Total Harga</strong></div>
                                <div class="fw-bold text-primary">Rp {{ number_format($this->totalPrice) }}</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Permintaan Khusus</label>
                            <textarea class="form-control @error('special_requests') is-invalid @enderror" rows="3" wire:model.defer="special_requests" placeholder="Opsional"></textarea>
                            @error('special_requests') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button class="btn btn-primary" type="submit" @disabled(empty($availableRoomTypes))>
                            Buat Reservasi
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Riwayat Reservasi</strong>
                </div>
                <div class="card-body">
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
                                @forelse ($reservations as $idx => $reservation)
                                    <tr>
                                        <td>{{ $reservations->firstItem() + $idx }}</td>
                                        <td>{{ $reservation->check_in_date->format('Y-m-d') }}</td>
                                        <td>{{ $reservation->check_out_date->format('Y-m-d') }}</td>
                                        <td><span class="badge bg-secondary">{{ $reservation->status }}</span></td>
                                        <td>
                                            @if($reservation->rooms && $reservation->rooms->count())
                                                {{ $reservation->rooms->pluck('room_number')->join(', ') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if(!in_array($reservation->status, ['Checked-in','Completed']))
                                                <button class="btn btn-sm btn-outline-danger" wire:click="cancelReservation({{ $reservation->id }})">Batalkan</button>
                                            @else
                                                <span class="text-muted">Tidak tersedia</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">Belum ada reservasi.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $reservations->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
