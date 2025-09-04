<div class="container py-4">
    <h1 class="h3 mb-3">Kelola Menu F&amp;B</h1>
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Kategori</span>
                    <button class="btn btn-sm btn-primary" wire:click="openCreateCategory">Tambah</button>
                </div>
                <div class="card-body">\n                    <div class="mb-2">
                        <input type="search" class="form-control" placeholder="Cari kategori..." wire:model.live.debounce.300ms="catSearch">
                    </div>
                    <ul class="list-group">
                        @forelse($categories as $c)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $c->name }} {!! $c->is_active ? '' : '<span class=\'badge bg-secondary\'>nonaktif</span>' !!}</span>
                                <span>
                                    <button class="btn btn-sm btn-warning" wire:click="editCategory({{ $c->id }})">Edit</button>
                                    <button class="btn btn-sm btn-danger" wire:click="deleteCategory({{ $c->id }})">Hapus</button>
                                </span>
                            </li>
                        @empty
                            <li class="list-group-item">Belum ada kategori.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="d-flex justify-content-end mb-3 gap-2">
                <button class="btn btn-primary" wire:click="create"><i class="bi bi-plus-lg me-1"></i> Tambah Item</button>
                <button class="btn btn-outline-secondary" wire:click="openCreateCategory"><i class="bi bi-tags me-1"></i> Tambah Kategori</button>
            </div>

            <div class="card">
                <div class="card-header">Daftar Item</div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Nama</th><th>Kategori</th><th>Harga</th><th>Populer</th><th>Status</th><th>Aksi</th></tr></thead>
                        <tbody>
                            @forelse($items as $it)
                                <tr>
                                    <td>{{ $it->name }}</td>
                                    <td>{{ $it->category?->name }}</td>
                                    <td>Rp{{ number_format($it->price,0,',','.') }}</td>
                                    <td>{!! $it->is_popular ? '<span class="badge bg-primary">Ya</span>' : '<span class="badge bg-light text-dark">Tidak</span>' !!}</td>
                                    <td>{!! $it->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' !!}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-warning" wire:click="editItem({{ $it->id }})">Edit</button>
                                            <button class="btn btn-danger" wire:click="deleteItem({{ $it->id }})">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-3">Belum ada item.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-body">{{ $items->links() }}</div>
            </div>
        </div>
    </div>
</div>

@if($isOpen)
    @include('livewire.fnb.menu-item-form')
@endif

@if($showCategoryModal)
<div class="modal fade show" style="display:block" tabindex="-1" aria-modal="true" role="dialog">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ $catId ? 'Edit Kategori' : 'Tambah Kategori' }}</h5>
        <button type="button" class="btn-close" wire:click="closeCategoryModal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
            <label class="form-label">Nama</label>
            <input type="text" class="form-control" wire:model="catName">
        </div>
        <div class="mb-2">
            <label class="form-label">Deskripsi</label>
            <input type="text" class="form-control" wire:model="catDesc">
                <div class="mb-2">
            <label class="form-label">Gambar (opsional)</label>
            <input type="file" class="form-control" wire:model="catImage" accept="image/*">
            @if ($catImage)
                <img src="{{ $catImage->temporaryUrl() }}" class="img-thumbnail mt-2" style="max-height: 120px;">
            @elseif ($catImageExisting)
                <img src="{{ str_starts_with($catImageExisting,'http') ? $catImageExisting : asset('storage/'.$catImageExisting) }}" class="img-thumbnail mt-2" style="max-height: 120px;">
            @endif
        </div>
        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="catActive" wire:model="catActive">
            <label class="form-check-label" for="catActive">Aktif</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" wire:click="closeCategoryModal">Tutup</button>
        <button type="button" class="btn btn-primary" wire:click="saveCategory">Simpan</button>
      </div>
    </div>
  </div>
</div>
<div class="modal-backdrop fade show"></div>
@endif
