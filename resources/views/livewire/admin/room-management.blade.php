<div>
    <style>
        .modal-backdrop { z-index: 1040 !important; }
        .modal { z-index: 1050 !important; }
    </style>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex mb-4 justify-content-between align-items-center flex-wrap">
                <div class="d-flex gap-2 mb-2 mb-md-0">
                    <select wire:model.live="perPage" class="form-select" style="width: auto;">
                        <option value="5">5 per halaman</option>
                        <option value="10">10 per halaman</option>
                        <option value="20">20 per halaman</option>
                        <option value="50">50 per halaman</option>
                    </select>
                    <input type="search" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari nomor atau tipe kamar...">
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.rooms.export', ['search' => $search]) }}" class="btn btn-outline-secondary">Export CSV</a>
                    <button class="btn btn-primary" wire:click="create">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Kamar
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="text-nowrap">No</th>
                            <th>Nomor Kamar</th>
                            <th>Tipe</th>
                            <th>Lantai</th>
                            <th>Harga/Malam</th>
                            <th>Status</th>
                            <th class="text-center text-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rooms as $index => $room)
                            <tr wire:key="{{ $room->id }}">
                                <td>{{ $rooms->firstItem() + $index }}</td>
                                <td><strong>{{ $room->room_number }}</strong></td>
                                <td>{{ $room->roomType->name ?? 'N/A' }}</td>
                                <td>{{ $room->floor }}</td>
                                <td>Rp {{ number_format($room->price_per_night, 0, ',', '.') }}</td>
                                <td>
                                    @if ($room->status == 'Available')
                                        <span class="badge bg-light-success">Tersedia</span>
                                    @elseif ($room->status == 'Occupied')
                                        <span class="badge bg-light-danger">Terisi</span>
                                    @else
                                        <span class="badge bg-light-warning">Dibesihkan</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-warning btn-sm" wire:click="edit({{ $room->id }})"><i class="bi bi-pencil-square"></i></button>
                                        {{-- Foto dikelola per tipe kamar, tombol dihapus --}}
                                        {{-- [UPDATE] Tambahkan kondisi disabled pada tombol hapus --}}
                                        <button 
                                            class="btn btn-danger btn-sm" 
                                            wire:click="$dispatch('swal:confirm', { id: {{ $room->id }}, method: 'destroy' })"
                                            {{ $room->status == 'Occupied' ? 'disabled' : '' }}
                                            title="{{ $room->status == 'Occupied' ? 'Kamar sedang terisi' : 'Hapus kamar' }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4">Tidak ada data kamar yang ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $rooms->links() }}
            </div>
        </div>
    </div>

    <div class="modal fade" id="roomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $roomId ? 'Edit Kamar' : 'Tambah Kamar Baru' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="store">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="room_number" class="form-label">Nomor Kamar <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('room_number') is-invalid @enderror" id="room_number" wire:model="room_number">
                                @error('room_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="floor" class="form-label">Lantai <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('floor') is-invalid @enderror" id="floor" wire:model="floor">
                                @error('floor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="room_type_id" class="form-label">Tipe Kamar <span class="text-danger">*</span></label>
                                <select class="form-select @error('room_type_id') is-invalid @enderror" id="room_type_id" wire:model="room_type_id">
                                    <option value="" selected>Pilih Tipe</option>
                                    @foreach($roomTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('room_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" wire:model="status">
                                    <option value="" selected>Pilih Status</option>
                                    <option value="Available">Available</option>
                                    <option value="Occupied">Occupied</option>
                                    <option value="Cleaning">Cleaning</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="price_per_night" class="form-label">Harga per Malam <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control @error('price_per_night') is-invalid @enderror" id="price_per_night" wire:model="price_per_night">
                            </div>
                            @error('price_per_night') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" wire:model="description" rows="3"></textarea>
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
</div>
