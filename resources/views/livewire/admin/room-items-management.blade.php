{{-- 
    =====================================================================================
    Improved UI/UX for Room Inventory Management by Gemini
    =====================================================================================
    Key Changes:
    1.  **Master-Detail Layout**: Changed the fundamental layout to a two-column (master-detail) view.
        -   The left column (col-md-4) consistently shows the list of rooms.
        -   The right column (col-md-8) shows the details of the selected room.
        -   This avoids the jarring full-page switch and allows users to quickly navigate between rooms without losing context.
    2.  **Tabbed Interface for Actions**: The right-hand detail panel is now organized with tabs.
        -   **Tab 1 (Default)**: "Daftar & Tambah Item" combines the item list and the add/edit form. This is the most common task, so it's front and center.
        -   **Tab 2**: "Tindakan Massal" groups all bulk operations (copying, applying templates) into one logical place.
        -   **Tab 3**: "Template Tipe Kamar" is dedicated to managing templates, keeping it separate from individual room inventory.
    3.  **Improved Room List**: The room list now uses a `list-group` instead of a table.
        -   It feels more interactive and clickable.
        -   The active room is highlighted, providing clear visual feedback.
    4.  **Better Information Hierarchy**:
        -   Filters are now logically placed. Room-level filters are with the room list, and item-level filters are with the item list.
        -   Headings are clearer, and cards are used to group related functionality.
    5.  **Enhanced User Guidance**:
        -   An initial placeholder state clearly instructs the user to select a room.
        -   Buttons and actions have been slightly re-worded for clarity.
        -   Consistent use of icons enhances visual communication.
    =====================================================================================
--}}

<div class="container-fluid py-4" x-data="{ activeTab: 'items' }">
    <div class="row g-4">

        <!-- Kolom Kiri: Daftar Kamar (Master View) -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <i class="bi bi-door-open-fill me-2"></i> Daftar Kamar
                    </h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <!-- Filter Kamar -->
                    <div class="row g-2 mb-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold">Tipe Kamar</label>
                            <select class="form-select form-select-sm" wire:model.live="roomTypeId">
                                <option value="">-- Semua Tipe --</option>
                                @foreach($types as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Cari Nomor Kamar</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="search" class="form-control" placeholder="Ketik nomor kamar..." wire:model.live.debounce.300ms="roomSearch">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Daftar List Kamar -->
                    <div class="list-group flex-grow-1">
                        @forelse(($roomPage ?? collect()) as $r)
                            <button type="button" 
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $r->id == $roomId ? 'active' : '' }}" 
                                    wire:click="viewRoom({{ $r->id }})">
                                <div>
                                    <h6 class="mb-0">No. {{ $r->room_number }}</h6>
                                    <small class="text-muted">{{ $r->roomType?->name ?: 'Tanpa Tipe' }}</small>
                                </div>
                                @php($st = $roomStatus[$r->id] ?? null)
                                @if($st==='complete')
                                    <span class="badge bg-success rounded-pill" title="Lengkap"><i class="bi bi-check-circle-fill"></i></span>
                                @elseif($st==='need')
                                    <span class="badge bg-warning rounded-pill" title="Kurang"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                @else
                                    <span class="badge bg-secondary rounded-pill">-</span>
                                @endif
                            </button>
                        @empty
                            <div class="text-center p-4 text-muted">
                                <i class="bi bi-inbox fs-2"></i>
                                <p class="mb-0 mt-2">Tidak ada kamar ditemukan.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination Kamar -->
                    @if(($roomPage ?? null) && $roomPage->hasPages())
                        <div class="mt-3">
                            {{ $roomPage->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Detail Inventori (Detail View) -->
        <div class="col-lg-8">
            @if(!$roomId)
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex justify-content-center align-items-center text-center text-muted">
                        <div>
                            <i class="bi bi-box-seam" style="font-size: 4rem;"></i>
                            <h4 class="mt-3">Manajemen Inventori</h4>
                            <p>Pilih kamar di sebelah kiri untuk melihat, menambah, <br>atau mengubah data inventori barang.</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-sm-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-sm-0">
                            <i class="bi bi-boxes me-2 text-primary"></i>Inventori Kamar <strong>No. {{ $selectedRoomNumber }}</strong>
                        </h5>
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item"><a class="nav-link" :class="{ 'active': activeTab === 'items' }" @click.prevent="activeTab = 'items'" href="#">Daftar & Tambah Item</a></li>
                            <li class="nav-item"><a class="nav-link" :class="{ 'active': activeTab === 'actions' }" @click.prevent="activeTab = 'actions'" href="#">Tindakan Massal</a></li>
                            <li class="nav-item"><a class="nav-link" :class="{ 'active': activeTab === 'template' }" @click.prevent="activeTab = 'template'" href="#">Template Tipe Kamar</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <!-- Tab 1: Daftar & Tambah Item -->
                        <div x-show="activeTab === 'items'" x-transition>
                            <div class="card border-light mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        @if($editId)
                                            <i class="bi bi-pencil-square me-2"></i>Ubah Item Inventori
                                        @else
                                            <i class="bi bi-plus-circle me-2"></i>Tambah Item Baru ke Kamar No. {{ $selectedRoomNumber }}
                                        @endif
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Nama Item <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="mis. Handuk">
                                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                                            <input type="number" min="0" class="form-control @error('quantity') is-invalid @enderror" wire:model="quantity" placeholder="mis. 2">
                                            @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Satuan</label>
                                            <input type="text" class="form-control @error('unit') is-invalid @enderror" wire:model="unit" placeholder="mis. buah">
                                            @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-2 d-grid align-self-end">
                                            @if($editId)
                                                <button class="btn btn-success" wire:click="update" wire:loading.attr="disabled"><i class="bi bi-save me-1"></i> Simpan</button>
                                            @else
                                                <button class="btn btn-primary" wire:click="add" wire:loading.attr="disabled"><i class="bi bi-plus-lg me-1"></i> Tambah</button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div>
                                            <small class="text-muted me-2">Isi cepat:</small>
                                            <button class="btn btn-xs btn-outline-secondary" wire:click="fillPreset('Kasur')">Kasur</button>
                                            <button class="btn btn-xs btn-outline-secondary" wire:click="fillPreset('Handuk')">Handuk</button>
                                            <button class="btn btn-xs btn-outline-secondary" wire:click="fillPreset('Sabun')">Sabun</button>
                                            <button class="btn btn-xs btn-outline-secondary" wire:click="fillPreset('Shampoo')">Shampoo</button>
                                        </div>
                                        @if($editId)
                                        <button class="btn btn-sm btn-link text-danger" wire:click="cancel">Batal Edit</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="m-0">Daftar Item di Kamar No. {{ $selectedRoomNumber }}</h6>
                                <input type="search" class="form-control w-auto" placeholder="Cari nama item..." wire:model.live.debounce.300ms="search">
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th><a href="#" class="text-decoration-none text-dark fw-bold" wire:click.prevent="sortBy('name')">Nama Item @if($sortField==='name')<i class="bi bi-caret-{{ $sortDir==='asc'?'up':'down' }}-fill"></i>@endif</a></th>
                                            <th class="text-end"><a href="#" class="text-decoration-none text-dark fw-bold" wire:click.prevent="sortBy('quantity')">Jumlah @if($sortField==='quantity')<i class="bi bi-caret-{{ $sortDir==='asc'?'up':'down' }}-fill"></i>@endif</a></th>
                                            <th><a href="#" class="text-decoration-none text-dark fw-bold" wire:click.prevent="sortBy('unit')">Satuan @if($sortField==='unit')<i class="bi bi-caret-{{ $sortDir==='asc'?'up':'down' }}-fill"></i>@endif</a></th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rows as $it)
                                        <tr>
                                            <td>{{ $it->name }}</td>
                                            <td class="text-end">{{ $it->quantity }}</td>
                                            <td>{{ $it->unit ?: '-' }}</td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" wire:click="edit({{ $it->id }})"><i class="bi bi-pencil"></i></button>
                                                    <button class="btn btn-outline-danger" x-data @click="$wire.dispatch('swal:confirm', { method: 'destroy', params: {{ $it->id }}, title: 'Hapus Item Ini?', text: 'Item \'{{ addslashes($it->name) }}\' akan dihapus.' })"><i class="bi bi-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center p-4 text-muted">Belum ada item inventori di kamar ini.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($rows->hasPages())
                                <div class="mt-3">{{ $rows->links() }}</div>
                            @endif
                        </div>

                        <!-- Tab 2: Tindakan Massal -->
                        <div x-show="activeTab === 'actions'" x-transition>
                             <div class="row">
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light"><h6 class="mb-0"><i class="bi bi-clipboard-plus me-2"></i>Salin Inventori Antar Kamar</h6></div>
                                        <div class="card-body">
                                            <div class="mb-3"><label class="form-label">Salin dari Kamar (Sumber)</label><select class="form-select" wire:model.live="sourceRoomId"><option value="">-- pilih sumber --</option>@foreach($rooms as $r)<option value="{{ $r->id }}">No. {{ $r->room_number }}</option>@endforeach</select></div>
                                            <div class="mb-3"><label class="form-label">Ke Kamar (Tujuan)</label><select class="form-select" wire:model="targetRoomId"><option value="">-- pilih tujuan --</option>@foreach($rooms as $r)<option value="{{ $r->id }}" @disabled($r->id == $sourceRoomId)>No. {{ $r->room_number }}</option>@endforeach</select></div>
                                            <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="overwrite" wire:model.live="overwrite"><label class="form-check-label" for="overwrite">Timpa item di kamar tujuan</label></div>
                                            <div class="d-grid"><button class="btn btn-info" @disabled(!$sourceRoomId || !$targetRoomId) x-data @click="$wire.dispatch('swal:confirm', { method: 'inventory:copy', title: 'Salin Item?', text: '{{ $overwrite ? 'Item tujuan akan ditimpa.' : 'Salin komposisi item kamar.' }}', confirmText: 'Ya, salin' })"><i class="bi bi-files me-1"></i> Salin ke 1 Kamar</button></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light"><h6 class="mb-0"><i class="bi bi-distribute-vertical me-2"></i>Tindakan ke Banyak Kamar</h6></div>
                                        <div class="card-body">
                                            <div class="mb-3"><label class="form-label">Pilih Kamar Tujuan</label><select class="form-select" wire:model.live="targetRoomIds" multiple size="5">@foreach($rooms as $r)<option value="{{ $r->id }}" @disabled($r->id == $sourceRoomId)>No. {{ $r->room_number }} @if(isset($roomStatus[$r->id]))({{ $roomStatus[$r->id] === 'complete' ? '✓' : '✗' }})@endif</option>@endforeach</select><small class="text-muted">Gunakan Ctrl/Cmd + Klik.</small></div>
                                            <div class="d-grid gap-2">
                                                 <button class="btn btn-primary" @disabled(!$sourceRoomId || empty($targetRoomIds)) x-data @click="$wire.dispatch('swal:confirm', { method: 'inventory:copyBulk', title: 'Salin ke Banyak Kamar?', text: '{{ $overwrite ? 'Item tujuan akan ditimpa.' : 'Salin item dari sumber ke kamar terpilih.' }}', confirmText: 'Ya, salin' })"><i class="bi bi-clipboard2-plus me-1"></i> Salin dari Sumber ke Terpilih</button>
                                                 <button class="btn btn-success" @disabled(!$roomTypeId || empty($targetRoomIds)) x-data @click="$wire.dispatch('swal:confirm', { method: 'inventory:applyTemplate', title: 'Terapkan Template?', text: 'Terapkan template ke kamar tujuan?{{ $overwrite ? ' (Mode Timpa aktif)' : '' }}', confirmText: 'Ya, terapkan' })"><i class="bi bi-journal-check me-1"></i> Terapkan Template ke Terpilih</button>
                                                 <hr class="my-2">
                                                 <button class="btn btn-danger" @disabled(!$roomId) x-data @click="$wire.dispatch('swal:confirm', { method: 'inventory:clear', title: 'Hapus Semua Item?', text: 'Hapus semua item dari kamar No. {{ $selectedRoomNumber }}? Tindakan ini tidak dapat dibatalkan.', confirmText: 'Ya, hapus' })"><i class="bi bi-trash3 me-1"></i> Kosongkan Kamar No. {{ $selectedRoomNumber }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Template Item -->
                        <div x-show="activeTab === 'template'" x-transition>
                             @if($roomTypeId)
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="bi bi-journal-plus me-2"></i>Template Item Standar</h6>
                                        <span class="badge bg-light text-dark">Tipe Kamar: {{ optional($types->firstWhere('id', (int)$roomTypeId))->name }}</span>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">Atur item standar untuk tipe kamar ini. Template ini dapat diterapkan ke banyak kamar sekaligus melalui tab "Tindakan Massal".</p>
                                        <div class="row g-2 align-items-end mb-3">
                                            <div class="col-md-4"><label class="form-label">Nama Item</label><input type="text" class="form-control" wire:model="templateName" placeholder="mis. Shampoo"></div>
                                            <div class="col-md-3"><label class="form-label">Jumlah</label><input type="number" min="0" class="form-control" wire:model="templateQuantity" placeholder="mis. 2"></div>
                                            <div class="col-md-3"><label class="form-label">Satuan</label><input type="text" class="form-control" wire:model="templateUnit" placeholder="mis. botol"></div>
                                            <div class="col-md-2 d-grid"><button class="btn btn-info" wire:click="addTemplateItem" wire:loading.attr="disabled"><i class="bi bi-plus"></i> Tambah</button></div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered align-middle">
                                                <thead class="table-light"><tr><th>Nama</th><th class="text-end">Jumlah</th><th>Satuan</th><th class="text-center">Aksi</th></tr></thead>
                                                <tbody>
                                                    @forelse($templates as $tpl)
                                                    <tr>
                                                        <td>{{ $tpl->name }}</td>
                                                        <td class="text-end">{{ $tpl->quantity }}</td>
                                                        <td>{{ $tpl->unit ?: '-' }}</td>
                                                        <td class="text-center"><button class="btn btn-xs btn-outline-danger" wire:click="deleteTemplateItem({{ $tpl->id }})"><i class="bi bi-trash"></i></button></td>
                                                    </tr>
                                                    @empty
                                                    <tr><td colspan="4" class="text-center p-3 text-muted">Belum ada item template.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning text-center">
                                    <i class="bi bi-exclamation-triangle-fill h4"></i>
                                    <p class="mb-0">Pilih <strong>Tipe Kamar</strong> di filter sebelah kiri untuk mengelola template item.</p>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
