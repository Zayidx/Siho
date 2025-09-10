<div class="">
    <div class="container-fluid py-4">
        <div class="row g-4">

            <!-- Kolom Kiri: Daftar Kamar (Master View) -->
            <div class="col-lg-4 col-md-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom p-2">
                        <h5 class="card-title mb-0 d-flex align-items-center">
                            <i class="bi bi-door-open-fill me-2 text-primary"></i> Daftar Kamar
                        </h5>
                    </div>
                    <div class="card-body d-flex flex-column p-3">
                        <!-- Filter Kamar -->
                        <div class="row g-2 mb-2">
                            <div class="col-12">
                                <label for="roomTypeFilter" class="form-label small fw-bold">Tipe Kamar</label>
                                <select id="roomTypeFilter" class="form-select form-select-sm"
                                    wire:model.live="roomTypeId">
                                    <option value="">-- Semua Tipe --</option>
                                    @foreach ($types as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="roomSearchFilter" class="form-label small fw-bold">Cari Nomor Kamar</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input id="roomSearchFilter" type="search" class="form-control"
                                        placeholder="Ketik nomor kamar..." wire:model.live.debounce.300ms="roomSearch">
                                </div>
                            </div>
                        </div>


                        <!-- Daftar List Kamar -->
                        <div class="list-group list-group-flush flex-grow-1 overflow-auto"
                            wire:loading.class="opacity-75" wire:target="viewRoom">
                            @forelse(($roomPage ?? collect()) as $r)
                                <button type="button"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $r->id == $roomId ? 'active' : '' }}"
                                    wire:click="viewRoom({{ $r->id }})" wire:loading.attr="disabled"
                                    wire:target="viewRoom({{ $r->id }})">
                                    <div>
                                        <h6 class="mb-0 fw-bold">Kamar {{ $r->room_number }}</h6>
                                        <small class="text-muted">{{ $r->roomType?->name ?: 'Tanpa Tipe' }}</small>
                                    </div>
                                    <div style="width: 18px;" class="text-center">
                                        <div wire:loading wire:target="viewRoom({{ $r->id }})">
                                            <span class="spinner-border spinner-border-sm" role="status"
                                                aria-hidden="true"></span>
                                        </div>
                                        <div wire:loading.remove wire:target="viewRoom({{ $r->id }})"></div>
                                    </div>
                                </button>
                            @empty
                                <div
                                    class="text-center p-4 text-muted d-flex flex-column justify-content-center align-items-center flex-grow-1">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <p class="mb-0 mt-2">Tidak ada kamar ditemukan.</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Pagination Kamar -->
                        @if (($roomPage ?? null) && $roomPage->hasPages())
                            <div class="mt-2">
                                {{ $roomPage->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Detail Inventori (Detail View) -->
            <div class="col-lg-8 col-md-7">
                @if (!$roomId)
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex justify-content-center align-items-center text-center text-muted">
                            <div>
                                <i class="bi bi-box-seam" style="font-size: 4rem;"></i>
                                <h4 class="mt-3">Manajemen Inventori</h4>
                                <p>Pilih kamar di sebelah kiri untuk melihat, menambah, <br>atau mengubah data inventori
                                    barang.
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card shadow-sm" x-data="{ activeTab: (localStorage.getItem('inv.tab') || 'items') }" x-init="$watch('activeTab', v => localStorage.setItem('inv.tab', v))">
                        <div class="card-header bg-white d-sm-flex justify-content-between align-items-center sticky-top border-bottom p-3"
                            style="top: -1px; z-index: 1020;">
                            <h5 class="card-title mb-2 mb-sm-0">
                                <i class="bi bi-boxes me-2 text-primary"></i>Inventori Kamar <strong
                                    class="text-primary">No.
                                    {{ $selectedRoomNumber }}</strong>
                            </h5>
                            <ul class="nav nav-pills nav-sm" role="tablist">
                                <li class="nav-item"><a class="nav-link" :class="{ 'active': activeTab === 'items' }"
                                        @click.prevent="activeTab = 'items'" href="#">Daftar & Tambah</a></li>
                                <li class="nav-item"><a class="nav-link" :class="{ 'active': activeTab === 'actions' }"
                                        @click.prevent="activeTab = 'actions'" href="#">Tindakan Massal</a></li>
                                <li class="nav-item"><a class="nav-link" :class="{ 'active': activeTab === 'template' }"
                                        @click.prevent="activeTab = 'template'" href="#">Template</a></li>
                            </ul>
                        </div>
                        <div class="card-body p-3 p-lg-4">
                            <!-- Tab 1: Daftar & Tambah Item -->
                            <div x-show="activeTab === 'items'" x-transition>
                                <div
                                    class="card @if ($editId) border-primary @else border-light @endif mb-4">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0">
                                            @if ($editId)
                                                <i class="bi bi-pencil-square me-2 text-primary"></i>Ubah Item Inventori
                                            @else
                                                <i class="bi bi-plus-circle me-2"></i>Tambah Item Baru
                                            @endif
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Nama Item <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('name') is-invalid @enderror"
                                                    wire:model="name" placeholder="mis. Handuk"
                                                    @disabled(!$canEdit)>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Jumlah <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" min="0"
                                                    class="form-control @error('quantity') is-invalid @enderror"
                                                    wire:model="quantity" placeholder="mis. 2"
                                                    @disabled(!$canEdit)>
                                                @error('quantity')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Satuan</label>
                                                <input type="text"
                                                    class="form-control @error('unit') is-invalid @enderror"
                                                    list="unitsOptions" wire:model="unit" placeholder="mis. buah"
                                                    @disabled(!$canEdit)>
                                                <datalist id="unitsOptions">
                                                    @foreach ($unitsOptions as $u)
                                                        <option value="{{ $u }}"></option>
                                                    @endforeach
                                                </datalist>
                                                @error('unit')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-2 d-grid align-self-end">
                                                @if ($editId)
                                                    <button class="btn btn-success" wire:click="update"
                                                        wire:loading.attr="disabled" @disabled(!$canEdit)><i
                                                            class="bi bi-save me-1"></i> Simpan</button>
                                                @else
                                                    <button class="btn btn-primary" wire:click="add"
                                                        wire:loading.attr="disabled" @disabled(!$canEdit)><i
                                                            class="bi bi-plus-lg me-1"></i> Tambah</button>
                                                @endif
                                            </div>
                                        </div>
                                        <div
                                            class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                                            <div>
                                                <small class="text-muted me-2">Isi cepat:</small>
                                                <button class="btn btn-sm btn-outline-secondary"
                                                    wire:click.prevent="fillPreset('Kasur')"
                                                    @disabled(!$canEdit)>Kasur</button>
                                                <button class="btn btn-sm btn-outline-secondary"
                                                    wire:click.prevent="fillPreset('Handuk')"
                                                    @disabled(!$canEdit)>Handuk</button>
                                                <button class="btn btn-sm btn-outline-secondary"
                                                    wire:click.prevent="fillPreset('Sabun')"
                                                    @disabled(!$canEdit)>Sabun</button>
                                                <button class="btn btn-sm btn-outline-secondary"
                                                    wire:click.prevent="fillPreset('Shampoo')"
                                                    @disabled(!$canEdit)>Shampoo</button>
                                            </div>
                                            @if ($editId)
                                                <button class="btn btn-sm btn-link text-danger"
                                                    wire:click="cancel">Batal
                                                    Ubah</button>
                                            @endif
                                        </div>
                                    </div>
                                    @if ($editId)
                                        <div class="card-footer bg-primary-subtle text-primary-emphasis small">
                                            <i class="bi bi-info-circle-fill me-1"></i> Anda sedang mengubah item:
                                            <strong>{{ $name }}</strong>. Klik "Batal Ubah" untuk kembali
                                            menambah item baru.
                                        </div>
                                    @endif
                                </div>

                                <hr>
                                <div
                                    class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                                    <h6 class="m-0">Daftar Item di Kamar Ini</h6>
                                    <input type="search" class="form-control form-control-sm w-100 w-sm-auto"
                                        placeholder="Cari nama item..." wire:model.live.debounce.300ms="search">
                                </div>
                                <!-- Item List -->

                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-striped align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th><a href="#" class="text-decoration-none text-dark fw-bold"
                                                        wire:click.prevent="sortBy('name')">Nama Item @if ($sortField === 'name')
                                                            <i
                                                                class="bi bi-caret-{{ $sortDir === 'asc' ? 'up' : 'down' }}-fill"></i>
                                                        @endif
                                                    </a>
                                                </th>
                                                <th class="text-center" style="width: 180px;"><a href="#"
                                                        class="text-decoration-none text-dark fw-bold"
                                                        wire:click.prevent="sortBy('quantity')">Jumlah
                                                        @if ($sortField === 'quantity')
                                                            <i
                                                                class="bi bi-caret-{{ $sortDir === 'asc' ? 'up' : 'down' }}-fill"></i>
                                                        @endif
                                                    </a>
                                                </th>
                                                <th style="width: 150px;"><a href="#"
                                                        class="text-decoration-none text-dark fw-bold"
                                                        wire:click.prevent="sortBy('unit')">Satuan @if ($sortField === 'unit')
                                                            <i
                                                                class="bi bi-caret-{{ $sortDir === 'asc' ? 'up' : 'down' }}-fill"></i>
                                                        @endif
                                                    </a>
                                                </th>
                                                <th class="text-center" style="width: 100px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($rows as $it)
                                                <tr wire:key="item-{{ $it->id }}">
                                                    <td>
                                                        <span class="fw-semibold">{{ $it->name }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <button class="btn btn-outline-secondary"
                                                                wire:click="decrement({{ $it->id }})"
                                                                title="Kurangi" @disabled(!$canEdit)><i
                                                                    class="bi bi-dash"></i></button>
                                                            <input type="number" min="0"
                                                                class="form-control text-center" style="width:50px"
                                                                value="{{ $it->quantity }}"
                                                                wire:change="updateQuantity({{ $it->id }}, $event.target.value)"
                                                                wire:loading.attr="disabled"
                                                                @disabled(!$canEdit)
                                                                wire:target="updateQuantity, increment, decrement">
                                                            <button class="btn btn-outline-secondary"
                                                                wire:click="increment({{ $it->id }})"
                                                                title="Tambah" @disabled(!$canEdit)><i
                                                                    class="bi bi-plus"></i></button>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <select class="form-select form-select-sm" aria-label="Satuan"
                                                            wire:change="updateUnit({{ $it->id }}, $event.target.value)"
                                                            @disabled(!$canEdit)>
                                                            <option value="" @selected(!$it->unit)>
                                                                (kosong)
                                                            </option>
                                                            @foreach ($unitsOptions as $u)
                                                                <option value="{{ $u }}"
                                                                    @selected($it->unit === $u)>{{ ucfirst($u) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-sm">
                                                            <button class="btn btn-outline-primary"
                                                                wire:click="edit({{ $it->id }})" title="Ubah"
                                                                @disabled(!$canEdit)><i
                                                                    class="bi bi-pencil"></i></button>
                                                            <button class="btn btn-outline-danger" title="Hapus"
                                                                x-data @disabled(!$canEdit)
                                                                @click="$wire.dispatch('swal:confirm', { method: 'destroy', id: {{ $it->id }}, title: 'Hapus Item Ini?', text: 'Item \'{{ addslashes($it->name) }}\' akan dihapus permanen.' })"><i
                                                                    class="bi bi-trash"></i></button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center p-4 text-muted">Belum ada
                                                        item inventori di
                                                        kamar ini.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if ($rows->hasPages())
                                    <div class="mt-3">{{ $rows->links() }}</div>
                                @endif
                            </div>

                            <!-- Tab 2: Tindakan Massal -->
                            <div x-show="activeTab === 'actions'" x-transition>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header bg-white border-bottom">
                                                <h6 class="mb-0"><i class="bi bi-clipboard-plus me-2"></i>Salin
                                                    Inventori Antar
                                                    Kamar</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3"><label class="form-label">Salin dari Kamar
                                                        (Sumber)</label><select class="form-select"
                                                        wire:model.live="sourceRoomId">
                                                        <option value="">-- pilih sumber --</option>
                                                        @foreach ($rooms as $r)
                                                            <option value="{{ $r->id }}">No.
                                                                {{ $r->room_number }}</option>
                                                        @endforeach
                                                    </select></div>
                                                <div class="mb-3"><label class="form-label">Ke Kamar
                                                        (Tujuan)</label><select class="form-select"
                                                        wire:model="targetRoomId">
                                                        <option value="">-- pilih tujuan --</option>
                                                        @foreach ($rooms as $r)
                                                            <option value="{{ $r->id }}"
                                                                @disabled($r->id == $sourceRoomId)>No.
                                                                {{ $r->room_number }}</option>
                                                        @endforeach
                                                    </select></div>
                                                <div class="form-check mb-1"><input class="form-check-input"
                                                        type="checkbox" id="overwrite"
                                                        wire:model.live="overwrite"><label class="form-check-label"
                                                        for="overwrite">Timpa item di kamar
                                                        tujuan</label></div>
                                                <small class="form-text text-muted d-block mb-3">Jika dicentang, semua
                                                    item di
                                                    tujuan akan dihapus sebelum item baru disalin.</small>
                                                <div class="d-grid"><button class="btn btn-info"
                                                        @disabled(!$sourceRoomId || !$targetRoomId) x-data
                                                        @click="$wire.dispatch('swal:confirm', { method: 'inventory:copy', title: 'Salin Item?', text: '{{ $overwrite ? 'Item tujuan akan DITIMPA.' : 'Salin komposisi item kamar sumber ke tujuan.' }}', confirmText: 'Ya, salin' })"><i
                                                            class="bi bi-files me-1"></i> Salin ke 1 Kamar</button>
                                                </div>
                                                @if (!$sourceRoomId || !$targetRoomId)
                                                    <small class="text-danger d-block mt-2">Pilih kamar sumber dan satu
                                                        kamar
                                                        tujuan.</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header bg-white border-bottom">
                                                <h6 class="mb-0"><i
                                                        class="bi bi-distribute-vertical me-2"></i>Tindakan ke
                                                    Banyak Kamar</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Pilih Kamar Tujuan
                                                        ({{ count($targetRoomIds) }}
                                                        terpilih)</label>
                                                    <div class="d-flex gap-2">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-secondary flex-grow-1"
                                                            data-bs-toggle="modal" data-bs-target="#roomPickerModal">
                                                            <i class="bi bi-list-check me-1"></i> Buka Pemilih Kamar
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                            wire:click="selectAllFiltered">
                                                            <i class="bi bi-check2-all me-1"></i> Pilih Semua Hasil
                                                            Filter
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="d-grid gap-2">
                                                    <button class="btn btn-primary" @disabled(!$sourceRoomId || empty($targetRoomIds))
                                                        x-data
                                                        @click="$wire.dispatch('swal:confirm', { method: 'inventory:copyBulk', title: 'Salin ke {{ count($targetRoomIds) }} Kamar?', text: '{{ $overwrite ? 'Item tujuan akan DITIMPA.' : 'Salin item dari sumber ke semua kamar terpilih.' }}', confirmText: 'Ya, salin' })"><i
                                                            class="bi bi-clipboard2-plus me-1"></i> Salin dari Sumber
                                                        ke
                                                        Terpilih</button>
                                                    <button class="btn btn-success" @disabled(!$roomTypeId || empty($targetRoomIds))
                                                        x-data
                                                        @click="$wire.dispatch('swal:confirm', { method: 'inventory:applyTemplate', title: 'Terapkan Template?', text: 'Terapkan template ke {{ count($targetRoomIds) }} kamar tujuan?{{ $overwrite ? ' (Mode Timpa aktif)' : '' }}', confirmText: 'Ya, terapkan' })"><i
                                                            class="bi bi-journal-check me-1"></i> Terapkan Template ke
                                                        Terpilih</button>
                                                    <hr class="my-2">
                                                    <button class="btn btn-danger" @disabled(!$roomId) x-data
                                                        @disabled(!$canEdit)
                                                        @click="$wire.dispatch('swal:confirm', { method: 'inventory:clear', title: 'Hapus Semua Item?', text: 'Hapus SEMUA item dari kamar No. {{ $selectedRoomNumber }}? Tindakan ini tidak dapat dibatalkan.', confirmText: 'Ya, hapus semua' })"><i
                                                            class="bi bi-trash3 me-1"></i> Kosongkan Kamar No.
                                                        {{ $selectedRoomNumber }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab 3: Template Item -->
                            <div x-show="activeTab === 'template'" x-transition>
                                @if ($roomTypeId)
                                    <div class="card border-info">
                                        <div
                                            class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><i class="bi bi-journal-plus me-2"></i>Template Item
                                                untuk Tipe Kamar
                                            </h6>
                                            <span
                                                class="badge bg-light text-dark">{{ optional($types->firstWhere('id', (int) $roomTypeId))->name }}</span>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted">Atur item standar untuk tipe kamar ini. Template ini
                                                dapat
                                                diterapkan ke banyak kamar sekaligus melalui tab "Tindakan Massal".</p>
                                            <div class="row g-2 align-items-end mb-3">
                                                <div class="col-md-4"><label class="form-label">Nama
                                                        Item</label><input type="text" class="form-control"
                                                        wire:model="templateName" placeholder="mis. Shampoo"
                                                        @disabled(!$canEdit)>
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Jumlah</label><input
                                                        type="number" min="0" class="form-control"
                                                        wire:model="templateQuantity" placeholder="mis. 2"
                                                        @disabled(!$canEdit)></div>
                                                <div class="col-md-3"><label class="form-label">Satuan</label><input
                                                        type="text" class="form-control" wire:model="templateUnit"
                                                        placeholder="mis. botol" @disabled(!$canEdit)>
                                                </div>
                                                <div class="col-md-2 d-grid"><button class="btn btn-info"
                                                        wire:click="addTemplateItem" wire:loading.attr="disabled"
                                                        @disabled(!$canEdit)><i class="bi bi-plus"></i>
                                                        Tambah</button></div>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered align-middle">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Nama</th>
                                                            <th class="text-end">Jumlah</th>
                                                            <th>Satuan</th>
                                                            <th class="text-center">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($templates as $tpl)
                                                            <tr>
                                                                <td>{{ $tpl->name }}</td>
                                                                <td class="text-end">{{ $tpl->quantity }}</td>
                                                                <td>{{ $tpl->unit ?: '-' }}</td>
                                                                <td class="text-center"><button
                                                                        class="btn btn-sm btn-outline-danger"
                                                                        wire:click="deleteTemplateItem({{ $tpl->id }})"><i
                                                                            class="bi bi-trash"></i></button></td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="4" class="text-center p-3 text-muted">
                                                                    Belum ada item
                                                                    template.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-warning text-center">
                                        <i class="bi bi-exclamation-triangle-fill h4"></i>
                                        <p class="mb-0">Pilih <strong>Tipe Kamar</strong> pada filter di sebelah kiri
                                            untuk
                                            mengelola template.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>

    @if ($showUndoBar)
        <div class="position-fixed bottom-0 end-0 m-3" style="z-index: 1080;">
            <div class="toast show align-items-center text-bg-dark border-0" role="alert" aria-live="assertive"
                aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        Inventori kamar dikosongkan. Batalkan?
                    </div>
                    <div class="d-flex align-items-center p-2 gap-2">
                        <button type="button" class="btn btn-sm btn-light" wire:click="undoClear">Undo</button>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" aria-label="Close"
                            wire:click="$set('showUndoBar', false)"></button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (!empty($roomId))

        <!-- Modal Checklist Kamar Tujuan -->

        <div class="modal fade" id="roomPickerModal" tabindex="-1" role="dialog" aria-hidden="true"
            wire:ignore.self>
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-list-check me-2"></i>Pilih Kamar Tujuan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="input-group input-group-sm mb-3 sticky-top"
                            style="top: -1rem; background: white; z-index: 2; padding-top: 1rem; margin-top: -1rem;">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" class="form-control" placeholder="Cari nomor kamar..."
                                wire:model.live.debounce.300ms="pickerSearch">
                        </div>
                        <div class="row row-cols-2 row-cols-md-3 g-2">
                            @php($pickerRooms = $rooms)
                            @forelse($pickerRooms as $r)
                                @if (!$pickerSearch || str_contains(strtolower((string) $r->room_number), strtolower((string) $pickerSearch)))
                                    <div class="col">
                                        <label
                                            class="form-check d-flex align-items-center gap-2 border rounded p-2 m-0 @if ($r->id == $sourceRoomId) bg-light text-muted pe-none @endif">
                                            <input class="form-check-input" type="checkbox"
                                                value="{{ $r->id }}" wire:model.live="targetRoomIds"
                                                @disabled($r->id == $sourceRoomId)>
                                            <span class="fw-bold">No. {{ $r->room_number }}</span>

                                        </label>
                                    </div>
                                @endif
                            @empty
                                <div class="col-12 text-center text-muted">Tidak ada kamar yang bisa dipilih.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
