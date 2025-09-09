<div class="modal fade show" style="display:block" tabindex="-1" aria-modal="true" role="dialog">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ $itemId ? 'Edit Item Menu' : 'Tambah Item Menu' }}</h5>
        <button type="button" class="btn-close" wire:click="closeItemModal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Kategori</label>
                <select class="form-select" wire:model="itemCategoryId">
                    <option value="">- pilih -</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nama</label>
                <input type="text" class="form-control" wire:model="itemName">
            </div>
            <div class="col-md-12">
                <label class="form-label">Deskripsi</label>
                <input type="text" class="form-control" wire:model="itemDesc">
            </div>
            <div class="col-md-6">
                <label class="form-label">Harga (Rp)</label>
                <input type="number" class="form-control" wire:model="itemPrice" min="0">
            </div>
            <div class="col-md-6">
                <label class="form-label">Gambar</label>
                <input type="file" class="form-control" wire:model="itemImage" accept="image/jpeg,image/png,image/webp">
                @if ($itemImage)
                    <img src="{{ $itemImage->temporaryUrl() }}" class="img-thumbnail mt-2" style="max-height: 120px;">
                @elseif ($itemImageExisting)
                    <img src="{{ str_starts_with($itemImageExisting,'http') ? $itemImageExisting : asset('storage/'.$itemImageExisting) }}" class="img-thumbnail mt-2" style="max-height: 120px;">
                @endif
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="itemActive" wire:model="itemActive">
                    <label class="form-check-label" for="itemActive">Aktif</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="itemPopular" wire:model="itemPopular">
                    <label class="form-check-label" for="itemPopular">Populer</label>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" wire:click="closeItemModal">Tutup</button>
        <button type="button" class="btn btn-primary" wire:click="saveItem">Simpan</button>
      </div>
    </div>
  </div>
</div>
<div class="modal-backdrop fade show"></div>
