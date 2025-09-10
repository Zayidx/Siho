<div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-2">
                    <select class="form-select" style="width:auto" wire:model.live="perPage">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                    <input type="search" class="form-control" placeholder="Cari kode/nama..."
                        wire:model.live.debounce.400ms="search">
                </div>
                <button class="btn btn-primary" wire:click="openModal()">Tambah Promo</button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Diskon</th>
                            <th>Aktif</th>
                            <th>Periode</th>
                            <th>Batas/Pakai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($promos as $idx => $p)
                            <tr>
                                <td>{{ $promos->firstItem() + $idx }}</td>
                                <td><code>{{ $p->code }}</code></td>
                                <td>{{ $p->name }}</td>
                                <td>{{ (float) $p->discount_rate * 100 }}%</td>
                                <td>
                                    @if ($p->active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="small">{{ optional($p->valid_from)->format('Y-m-d H:i') ?: '-' }} —
                                    {{ optional($p->valid_to)->format('Y-m-d H:i') ?: '-' }}</td>
                                <td>{{ $p->usage_limit ?: '∞' }} / {{ $p->used_count }}</td>
                                <td>
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary"
                                            wire:click="openModal({{ $p->id }})">Edit</button>
                                        <button class="btn btn-sm btn-outline-danger"
                                            wire:click="delete({{ $p->id }})">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada promo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $promos->links() }}
        </div>
    </div>

    <div class="modal fade" id="promoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $promoId ? 'Edit Promo' : 'Tambah Promo' }}</h5>
                    <button class="btn-close" wire:click="closeModal" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Kode (huruf/angka, tanpa spasi)</label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror"
                            wire:model.defer="code" placeholder="HEMAT10">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                            wire:model.defer="name" placeholder="Diskon 10% Akhir Pekan">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Diskon (0 - 1)</label>
                        <input type="number" step="0.01"
                            class="form-control @error('discount_rate') is-invalid @enderror"
                            wire:model.defer="discount_rate" placeholder="0.10">
                        @error('discount_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Berlaku untuk Tipe Kamar (opsional)</label>
                        <select class="form-select @error('apply_room_type_id') is-invalid @enderror"
                            wire:model.defer="apply_room_type_id">
                            <option value="">Semua Tipe</option>
                            @foreach ($roomTypes as $rt)
                                <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                            @endforeach
                        </select>
                        @error('apply_room_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-2 form-check">
                        <input class="form-check-input" type="checkbox" wire:model.defer="active" id="active">
                        <label class="form-check-label" for="active">Aktif</label>
                    </div>
                    <div class="row g-2">
                        <div class="col">
                            <label class="form-label">Berlaku dari</label>
                            <input type="datetime-local" class="form-control @error('valid_from') is-invalid @enderror"
                                wire:model.defer="valid_from">
                            @error('valid_from')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col">
                            <label class="form-label">Berlaku sampai</label>
                            <input type="datetime-local" class="form-control @error('valid_to') is-invalid @enderror"
                                wire:model.defer="valid_to">
                            @error('valid_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-2 mt-2">
                        <label class="form-label">Batas Pemakaian (opsional)</label>
                        <input type="number" class="form-control @error('usage_limit') is-invalid @enderror"
                            wire:model.defer="usage_limit" placeholder="cth. 100">
                        @error('usage_limit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="closeModal" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" wire:click="save">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>
