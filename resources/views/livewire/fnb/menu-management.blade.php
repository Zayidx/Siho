{{-- 
    =====================================================================================
    UI/UX F&B Menu Management yang Ditingkatkan oleh Gemini
    =====================================================================================
    Perubahan Utama (Revisi):
    1.  Layout Master-Detail: Tetap dipertahankan untuk alur kerja yang intuitif.
    2.  Form Modal Popup: Form untuk menambah/mengedit Kategori dan Item
        kini menggunakan modal popup untuk menjaga tampilan utama tetap bersih dan
        fokus pada daftar data.
    3.  Tombol Aksi Kontekstual: Tombol "Tambah Item" muncul di header kanan
        setelah sebuah kategori dipilih.
    4.  Estetika Bersih & Umpan Balik Visual:
        - Kategori yang aktif ditandai dengan jelas.
        - Indikator loading (`wire:loading`) memberikan umpan balik saat ada aksi.
    5.  Tampilan Responsif: Daftar item tetap responsif (tabel di desktop,
        kartu di mobile).
--}}

<div>
    <div class="container-fluid py-4" x-data>
        <div class="row g-4">
            <!-- Kolom Kiri: Daftar Kategori Menu (Master View) -->
            <div class="col-lg-4 col-md-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-tags-fill me-2 text-primary"></i> Kategori Menu
                        </h5>
                        <button class="btn btn-sm btn-primary" wire:click="openCreateCategory"><i
                                class="bi bi-plus-lg"></i> Tambah</button>
                    </div>
                    <div class="card-body d-flex flex-column p-2">
                        <div class="px-2 pb-2">
                            <input type="search" class="form-control form-control-sm" placeholder="Cari kategori..."
                                wire:model.live.debounce.300ms="catSearch">
                        </div>

                        <div class="list-group list-group-flush flex-grow-1 overflow-auto"
                            wire:loading.class="opacity-50" wire:target="selectCategory">
                            @forelse($categories as $c)
                                <div
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center pe-2 rounded-2 mb-1 {{ $c->id == $selectedCategoryId ? 'active' : '' }}">
                                    <a href="#"
                                        class="text-decoration-none stretched-link py-2 ps-2 {{ $c->id == $selectedCategoryId ? 'text-white' : 'text-dark' }}"
                                        wire:click.prevent="selectCategory({{ $c->id }})">
                                        <span class="fw-bold">{{ $c->name }}</span>
                                        {!! !$c->is_active ? '<small class="badge bg-secondary rounded-pill fw-normal ms-2">nonaktif</small>' : '' !!}
                                    </a>
                                    <div class="btn-group z-2">
                                        <button
                                            class="btn btn-sm {{ $c->id == $selectedCategoryId ? 'btn-light' : 'btn-outline-secondary' }}"
                                            wire:click.stop="editCategory({{ $c->id }})"><i
                                                class="bi bi-pencil"></i></button>
                                        <button
                                            class="btn btn-sm {{ $c->id == $selectedCategoryId ? 'btn-light' : 'btn-outline-secondary' }}"
                                            wire:click.stop="deleteCategory({{ $c->id }})"><i
                                                class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                            @empty
                                <div
                                    class="text-center p-4 text-muted d-flex flex-column justify-content-center align-items-center flex-grow-1">
                                    <i class="bi bi-inbox fs-2"></i>
                                    <p class="mb-0 mt-2">Belum ada kategori.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Detail Item Menu (Detail View) -->
            <div class="col-lg-8 col-md-7">
                @if (!$selectedCategoryId)
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex justify-content-center align-items-center text-center text-muted">
                            <div>
                                <i class="bi bi-cup-straw" style="font-size: 4rem;"></i>
                                <h4 class="mt-3">Manajemen Item Menu</h4>
                                <p>Pilih kategori di sebelah kiri untuk melihat item menu.</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-bottom sticky-top">
                            <div
                                class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-list-stars me-2 text-primary"></i> Item:
                                    <strong>{{ $selectedCategory->name ?? '...' }}</strong>
                                </h5>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="search" class="form-control form-control-sm" style="max-width: 220px;"
                                        placeholder="Cari item..." wire:model.live.debounce.300ms="itemSearch">
                                    <button class="btn btn-sm btn-primary" wire:click="openCreateItem"><i
                                            class="bi bi-plus-lg"></i> Tambah Item</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Daftar Item -->
                            <div wire:loading.class="opacity-50" class="mt-3">
                                @if ($items->isNotEmpty())
                                    <!-- Tampilan Desktop/Tablet (Tabel) -->
                                    <div class="table-responsive d-none d-md-block">
                                        <table class="table table-hover table-bordered table-striped align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 80px;" class="text-center">Gambar</th>
                                                    <th>Nama Item</th>
                                                    <th class="text-end">Harga</th>
                                                    <th class="text-center">Status</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($items as $it)
                                                    <tr>
                                                        <td class="text-center p-1"><img src="{{ $it->image_url }}"
                                                                class="img-fluid rounded" alt="{{ $it->name }}"
                                                                style="width:64px; height:64px; object-fit:cover;"
                                                                loading="lazy"></td>
                                                        <td>
                                                            <div class="fw-bold">{{ $it->name }}</div>
                                                            @if ($it->is_popular)
                                                                <span
                                                                    class="badge bg-warning text-dark fw-normal">Populer</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            Rp{{ number_format($it->price, 0, ',', '.') }}</td>
                                                        <td class="text-center">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input class="form-check-input" type="checkbox"
                                                                    role="switch" id="active-{{ $it->id }}"
                                                                    wire:click="toggleItemStatus({{ $it->id }}, 'is_active')"
                                                                    @checked($it->is_active)>
                                                                <label class="form-check-label"
                                                                    for="active-{{ $it->id }}">{{ $it->is_active ? 'Aktif' : 'Nonaktif' }}</label>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-primary"
                                                                    wire:click="editItem({{ $it->id }})"><i
                                                                        class="bi bi-pencil"></i></button>
                                                                <button class="btn btn-outline-danger"
                                                                    wire:click="deleteItem({{ $it->id }})"><i
                                                                        class="bi bi-trash"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Tampilan Mobile (Kartu) -->
                                    <div class="d-md-none vstack gap-3">
                                        @foreach ($items as $it)
                                            <div class="card">
                                                <div class="card-body d-flex gap-3 align-items-start">
                                                    <img src="{{ $it->image_url }}" alt="{{ $it->name }}"
                                                        class="rounded" style="width:64px;height:64px;object-fit:cover;"
                                                        loading="lazy">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <div class="fw-semibold">{{ $it->name }}</div>
                                                                <div class="small text-muted">
                                                                    Rp{{ number_format($it->price, 0, ',', '.') }}
                                                                </div>
                                                                @if ($it->is_popular)
                                                                    <span
                                                                        class="badge bg-warning text-dark fw-normal mt-1">Populer</span>
                                                                @endif
                                                            </div>
                                                            <span
                                                                class="badge {{ $it->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $it->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                                        </div>
                                                        <div class="mt-2 pt-2 border-top d-flex gap-2">
                                                            <button class="btn btn-sm btn-outline-primary w-100"
                                                                wire:click="editItem({{ $it->id }})"><i
                                                                    class="bi bi-pencil"></i> Ubah</button>
                                                            <button class="btn btn-sm btn-outline-danger w-100"
                                                                wire:click="deleteItem({{ $it->id }})"><i
                                                                    class="bi bi-trash"></i> Hapus</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if ($items->hasPages())
                                        <div class="mt-3">{{ $items->links() }}</div>
                                    @endif
                                @else
                                    <div class="text-center p-4 text-muted">
                                        <i class="bi bi-clipboard-x fs-2"></i>
                                        <p class="mb-0 mt-2">Belum ada item untuk kategori ini.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Kategori (always in DOM; controlled via JS) --}}
    <div class="modal fade" id="categoryModal" tabindex="-1" role="dialog" wire:ignore.self>
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $catId ? 'Edit Kategori' : 'Tambah Kategori' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeCategoryModal"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('catName') is-invalid @enderror"
                            wire:model="catName" placeholder="mis. Makanan Utama">
                        @error('catName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" wire:model="catDesc" rows="2" placeholder="mis. Aneka hidangan nasi dan mie"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar (opsional)</label>
                        <input type="file" class="form-control form-control-sm" wire:model="catImage"
                            accept="image/jpeg,image/png,image/webp" id="catImage"
                            wire:key="catImage-{{ (int) $showCategoryModal }}">
                        <div wire:loading wire:target="catImage" class="text-muted small mt-1">Mengunggah...</div>
                        @if ($catImage)
                            <img src="{{ $catImage->temporaryUrl() }}" class="img-thumbnail mt-2"
                                style="max-height: 120px;">
                        @elseif ($catImageExisting)
                            <img src="{{ str_starts_with($catImageExisting, 'http') ? $catImageExisting : asset('storage/' . $catImageExisting) }}"
                                class="img-thumbnail mt-2" style="max-height: 120px;">
                        @endif
                        @error('catImage')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="catActive"
                            wire:model="catActive">
                        <label class="form-check-label" for="catActive">Aktifkan Kategori</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeCategoryModal">Tutup</button>
                    <button type="button" class="btn btn-primary" wire:click="saveCategory"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveCategory">Simpan</span>
                        <span wire:loading wire:target="saveCategory">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Item Menu (always in DOM; controlled via JS) --}}
    <div class="modal fade" id="itemModal" tabindex="-1" role="dialog" wire:ignore.self>
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $itemId ? 'Ubah Item Menu' : 'Tambah Item Baru' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeItemModal"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label">Nama Item <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('itemName') is-invalid @enderror"
                                wire:model="itemName" placeholder="mis. Nasi Goreng Spesial">
                            @error('itemName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Harga <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" min="0"
                                    class="form-control @error('itemPrice') is-invalid @enderror"
                                    wire:model="itemPrice" placeholder="mis. 25000">
                            </div>
                            @error('itemPrice')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi Singkat</label>
                            <textarea class="form-control" wire:model="itemDesc" rows="2"
                                placeholder="mis. Nasi goreng dengan telur, ayam, dan bakso"></textarea>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">Gambar (opsional)</label>
                            <input type="file" class="form-control form-control-sm" wire:model="itemImage"
                                accept="image/jpeg,image/png,image/webp" id="itemImage"
                                wire:key="itemImage-{{ (int) $showItemModal }}">
                            <div wire:loading wire:target="itemImage" class="text-muted small mt-1">Mengunggah...
                            </div>
                            @if ($itemImage)
                                <img src="{{ $itemImage->temporaryUrl() }}" class="img-thumbnail mt-2"
                                    style="max-height: 100px;">
                            @elseif ($itemImageExisting)
                                <img src="{{ str_starts_with($itemImageExisting, 'http') ? $itemImageExisting : asset('storage/' . $itemImageExisting) }}"
                                    class="img-thumbnail mt-2" style="max-height: 100px;">
                            @endif
                            @error('itemImage')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-5 d-flex align-items-end">
                            <div>
                                <div class="form-check form-switch mb-2"><input class="form-check-input"
                                        type="checkbox" role="switch" id="modalItemActive"
                                        wire:model="itemActive"><label class="form-check-label"
                                        for="modalItemActive">Aktif</label></div>
                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox"
                                        role="switch" id="modalItemPopular" wire:model="itemPopular"><label
                                        class="form-check-label" for="modalItemPopular">Populer</label></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeItemModal">Tutup</button>
                    <button type="button" class="btn btn-primary" wire:click="saveItem"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveItem"><i class="bi bi-save me-1"></i> Simpan</span>
                        <span wire:loading wire:target="saveItem">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
