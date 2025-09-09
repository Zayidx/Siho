<div>
    <style>
        .modal-backdrop { z-index: 1040 !important; }
        .modal { z-index: 1050 !important; }
        .room-counter {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
        }
        .room-counter:last-child {
            border-bottom: none;
        }
        .room-counter-controls {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .room-counter-controls .count {
            font-weight: 500;
            min-width: 20px;
            text-align: center;
        }
    </style>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex mb-4 justify-content-between align-items-center flex-wrap">
                <div class="d-flex gap-2 mb-2 mb-md-0 align-items-center flex-wrap">
                    <select wire:model.live="perPage" class="form-select" style="width: auto;">
                        <option value="5">5 per halaman</option>
                        <option value="10">10 per halaman</option>
                        <option value="20">20 per halaman</option>
                    </select>
                    <select wire:model.live="filterStatus" class="form-select" style="width:auto;">
                        <option value="">Semua Status</option>
                        <option value="Confirmed">Confirmed</option>
                        <option value="Checked-in">Checked-in</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                    <input type="date" wire:model.live="startDate" class="form-control" style="width:auto;" title="Mulai">
                    <input type="date" wire:model.live="endDate" class="form-control" style="width:auto;" title="Selesai">
                    <input type="search" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari nama tamu...">
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.reservations.export', ['search' => $search, 'status' => $filterStatus, 'start' => $startDate, 'end' => $endDate]) }}" class="btn btn-outline-secondary">
                        Export CSV
                    </a>
                    <button class="btn btn-primary" wire:click="create">
                        <i class="bi bi-plus-circle me-2"></i>Buat Reservasi
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                @if($search || $filterStatus || $startDate || $endDate)
                    <div class="mb-2">
                        <span class="me-2">Filter aktif:</span>
                        @if($search)
                            <span class="badge bg-secondary me-1">Cari: {{ $search }}</span>
                        @endif
                        @if($filterStatus)
                            <span class="badge bg-secondary me-1">Status: {{ $filterStatus }}</span>
                        @endif
                        @if($startDate)
                            <span class="badge bg-secondary me-1">Mulai: {{ $startDate }}</span>
                        @endif
                        @if($endDate)
                            <span class="badge bg-secondary me-1">Selesai: {{ $endDate }}</span>
                        @endif
                        <button class="btn btn-sm btn-link" wire:click="clearFilters">Reset</button>
                    </div>
                @endif
                <table class="table table-hover table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="text-nowrap">No</th>
                            <th>Nama Tamu</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Kamar</th>
                            <th class="text-nowrap">Permintaan</th>
                            <th>Status</th>
                            <th class="text-center text-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reservations as $index => $reservation)
                            <tr wire:key="{{ $reservation->id }}">
                                <td>{{ $reservations->firstItem() + $index }}</td>
                                <td>{{ $reservation->guest->full_name ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($reservation->check_out_date)->format('d M Y') }}</td>
                                <td>
                                    <!-- [DIUBAH] Nomor kamar sekarang bisa diklik -->
                                    @foreach($reservation->rooms as $room)
                                        <button type="button" class="btn btn-sm bg-light-secondary text-dark border-0 p-1 me-1 mb-1" 
                                                wire:click="viewRoom({{ $room->id }})" 
                                                title="Lihat detail kamar {{ $room->room_number }}">
                                            {{ $room->room_number }}
                                        </button>
                                    @endforeach
                                </td>
                                <td class="small" style="max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $reservation->special_requests }}">
                                    {{ $reservation->special_requests ?: '-' }}
                                </td>
                                <td>
                                    @if ($reservation->status == 'Completed')
                                        <span class="badge bg-light-success">Selesai</span>
                                    @elseif ($reservation->status == 'Checked-in')
                                        <span class="badge bg-light-info">Check-in</span>
                                    @elseif ($reservation->status == 'Cancelled')
                                        <span class="badge bg-light-danger">Dibatalkan</span>
                                    @else
                                        <span class="badge bg-light-primary">Dikonfirmasi</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-warning btn-sm" wire:click="edit({{ $reservation->id }})"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-danger btn-sm" wire:click="$dispatch('swal:confirm', { id: {{ $reservation->id }}, method: 'destroy' })"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4">Tidak ada data reservasi yang ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $reservations->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Reservasi -->
    <div class="modal fade" id="reservationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $reservationId ? 'Edit Reservasi' : 'Buat Reservasi Baru' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="store">
                        <div class="btn-group w-100 mb-3" role="group">
                            <button type="button" class="btn {{ !$isCreatingGuest ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="$set('isCreatingGuest', false)">Pilih Tamu yang Ada</button>
                            <button type="button" class="btn {{ $isCreatingGuest ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="$set('isCreatingGuest', true)">Buat Tamu Baru</button>
                        </div>

                        @if ($isCreatingGuest)
                            <div class="border rounded p-3 mb-3">
                                <h5>Detail Tamu Baru</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="newGuest_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('newGuest_name') is-invalid @enderror" id="newGuest_name" wire:model="newGuest_name">
                                        @error('newGuest_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="newGuest_email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('newGuest_email') is-invalid @enderror" id="newGuest_email" wire:model="newGuest_email">
                                        @error('newGuest_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="newGuest_phone" class="form-label">Telepon <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('newGuest_phone') is-invalid @enderror" id="newGuest_phone" wire:model="newGuest_phone">
                                        @error('newGuest_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="mb-3">
                                <label for="guest_id" class="form-label">Tamu <span class="text-danger">*</span></label>
                                <select class="form-select @error('guest_id') is-invalid @enderror" id="guest_id" wire:model="guest_id">
                                    <option value="" selected>Pilih Tamu</option>
                                    @foreach ($guests as $guest)
                                        <option value="{{ $guest->id }}">{{ $guest->full_name }}</option>
                                    @endforeach
                                </select>
                                @error('guest_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="check_in_date" class="form-label">Tanggal Check-in <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('check_in_date') is-invalid @enderror" id="check_in_date" wire:model="check_in_date">
                                @error('check_in_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="check_out_date" class="form-label">Tanggal Check-out <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('check_out_date') is-invalid @enderror" id="check_out_date" wire:model="check_out_date">
                                @error('check_out_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pilih Kamar <span class="text-danger">*</span></label>
                            <div class="border rounded">
                                @forelse($availableRoomTypes as $typeId => $details)
                                    <div class="room-counter">
                                        <div>
                                            <div class="fw-bold">{{ $details['name'] }}</div>
                                            <small class="text-muted">{{ $details['available_count'] }} kamar tersedia</small>
                                        </div>
                                        <div class="room-counter-controls">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="decrementRoomType({{ $typeId }})" @disabled(($selectedRoomTypes[$typeId] ?? 0) <= 0)>-</button>
                                            <span class="count">{{ $selectedRoomTypes[$typeId] ?? 0 }}</span>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="incrementRoomType({{ $typeId }})" @disabled(($selectedRoomTypes[$typeId] ?? 0) >= $details['available_count'])>+</button>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted p-3 mb-0">Tidak ada tipe kamar yang tersedia saat ini.</p>
                                @endforelse
                            </div>
                            @error('selectedRoomTypes') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" wire:model="status">
                                <option value="Confirmed">Confirmed</option>
                                <option value="Checked-in">Checked-in</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="special_requests" class="form-label">Permintaan Khusus</label>
                            <textarea class="form-control" id="special_requests" wire:model="special_requests" rows="2"></textarea>
                        </div>

                        <div class="modal-footer pb-0">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="store">Simpan</span>
                                <span wire:loading wire:target="store" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span wire:loading wire:target="store">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- [MODAL BARU] Untuk Detail Kamar -->
    <div class="modal fade" id="roomDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Kamar: {{ $viewingRoom?->room_number ?: '-' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeRoomModal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-borderless table-sm">
                        <tbody>
                            <tr>
                                <th style="width: 35%;">Nomor Kamar</th>
                                <td>: {{ $viewingRoom?->room_number ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tipe Kamar</th>
                                <td>: {{ $viewingRoom?->roomType?->name ?: 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>: {!! $viewingRoom?->status ? '<span class="badge '.($viewingRoom->status == 'Available' ? 'bg-success' : 'bg-warning').'">'.e($viewingRoom->status).'</span>' : '-' !!}</td>
                            </tr>
                            {{-- Asumsi ada kolom 'price' dan 'description' di tabel rooms --}}
                            @if($viewingRoom && isset($viewingRoom->price))
                            <tr>
                                <th>Harga per Malam</th>
                                <td>: Rp {{ number_format($viewingRoom->price, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if($viewingRoom && !empty($viewingRoom->description))
                            <tr>
                                <th>Deskripsi</th>
                                <td>: {{ $viewingRoom->description }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeRoomModal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- [DIUBAH] Backdrop untuk semua modal -->
    <!-- Backdrop handled by Bootstrap or JS fallback -->
</div>
