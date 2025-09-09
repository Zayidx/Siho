{{-- 
    =====================================================================================
    Improved UI/UX for Room Inventory Management by Gemini (v2)
    =====================================================================================
    Key Changes from Previous Version:
    1.  **Cleaner Aesthetics**: Removed all gray `bg-light` backgrounds from card headers, 
        replacing them with `bg-white` and a subtle bottom border. This creates a more modern,
        spacious, and less "heavy" interface.
    2.  **Enhanced Interactivity & Feedback**:
        -   Added a loading spinner to the room list items. When a user clicks a room, 
            the status badge is replaced by a spinner, providing clear feedback that 
            data is being loaded.
        -   The entire room list now has a subtle opacity decrease during loading to 
            prevent double-clicks.
    3.  **Improved Add/Edit Form UX**:
        -   When a user clicks "Ubah" (Edit), the form card now gets a distinct blue border
          and a footer message indicating which item is being edited. This immediately
          clarifies the form's current state (editing vs. adding).
    4.  **Clearer Bulk Actions**: Added a descriptive helper text below the "Timpa item" 
        (Overwrite) checkbox to explain exactly what the option does, reducing potential
        user error.
    5.  **Visual Polish & Consistency**:
        -   Replaced non-standard `btn-xs` class with the standard Bootstrap `btn-sm` for
          better consistency and maintainability.
        -   Adjusted spacing and alignment for a more polished final look.
    =====================================================================================
--}}

<div class="container-fluid py-4" x-data="{ activeTab: 'items' }">
    <div class="row g-4">

        <!-- Kolom Kiri: Daftar Kamar (Master View) -->
        <div class="col-sm-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <i class="bi bi-door-open-fill me-2 text-primary"></i> Daftar Kamar
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
                    <div class="small text-muted mb-2">
                        <i class="bi bi-check-circle-fill text-success me-1"></i>Lengkap
                        <i class="bi bi-exclamation-triangle-fill text-warning ms-3 me-1"></i>Kurang
                    </div>
                    
                    <!-- Daftar List Kamar -->
                    <div class="list-group flex-grow-1 overflow-auto" wire:loading.class="opacity-75" wire:target="viewRoom">
                        @forelse(($roomPage ?? collect()) as $r)
                            <button type="button" 
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $r->id == $roomId ? 'active' : '' }}" 
                                    wire:click="viewRoom({{ $r->id }})" wire:loading.attr="disabled" wire:target="viewRoom({{ $r->id }})">
                                <div>
                                    <h6 class="mb-0">No. {{ $r->room_number }}</h6>
                                    <small class="text-muted">{{ $r->roomType?->name ?: 'Tanpa Tipe' }}</small>
                                </div>
                                <div style="width: 20px;" class="text-center">
                                    <div wire:loading wire:target="viewRoom({{ $r->id }})">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    </div>
                                    <div wire:loading.remove wire:target="viewRoom({{ $r->id }})">
                                        @php($st = $roomStatus[$r->id] ?? null)
                                        @if($st==='complete')
                                            <span class="badge bg-success rounded-pill" title="Lengkap"><i class="bi bi-check-circle-fill"></i></span>
                                        @elseif($st==='need')
                                            <span class="badge bg-warning rounded-pill" title="Kurang"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill">-</span>
                                        @endif
                                    </div>
                                </div>
                            </button>
                        @empty
                            <div class="text-center p-4 text-muted d-flex flex-column justify-content-center flex-grow-1">
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
        <div class="col-sm-8">
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
            <div class="card shadow-sm" x-data="{ activeTab: (localStorage.getItem('inv.tab')||'items') }" x-init="$watch('activeTab', v => localStorage.setItem('inv.tab', v))">
                    <div class="card-header bg-white d-sm-flex justify-content-between align-items-center sticky-top" style="z-index:5;">
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
                            <div class="card @if($editId) border-primary @else border-light @endif mb-4">
                                <div class="card-header bg-white">
                                    <h6 class="mb-0">
                                        @if($editId)
                                            <i class="bi bi-pencil-square me-2 text-primary"></i>Ubah Item Inventori
                                        @else
                                            <i class="bi bi-plus-circle me-2"></i>Tambah Item Baru
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
                                            <button class="btn btn-sm btn-outline-secondary" wire:click="fillPreset('Kasur')">Kasur</button>
                                            <button class="btn btn-sm btn-outline-secondary" wire:click="fillPreset('Handuk')">Handuk</button>
                                            <button class="btn btn-sm btn-outline-secondary" wire:click="fillPreset('Sabun')">Sabun</button>
                                            <button class="btn btn-sm btn-outline-secondary" wire:click="fillPreset('Shampoo')">Shampoo</button>
                                        </div>
                                        @if($editId)
                                        <button class="btn btn-sm btn-link text-danger" wire:click="cancel">Batal Edit</button>
                                        @endif
                                    </div>
                                </div>
                                @if($editId)
                                <div class="card-footer bg-primary-subtle text-primary-emphasis">
                                    <small><i class="bi bi-info-circle-fill me-1"></i> Anda sedang mengubah item: <strong>{{ $name }}</strong>. Klik "Batal Edit" untuk kembali menambah item baru.</small>
                                </div>
                                @endif
                            </div>
                            
                            <hr>
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                                <h6 class="m-0">Daftar Item di Kamar No. {{ $selectedRoomNumber }}</h6>
                                <input type="search" class="form-control form-control-sm w-100 w-sm-auto" placeholder="Cari nama item..." wire:model.live.debounce.300ms="search">
                            </div>
                            <!-- Desktop/Tablet Table -->
                            <div class="table-responsive d-none d-sm-block">
                                <table class="table table-hover table-bordered table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th><a href="#" class="text-decoration-none text-dark fw-bold" wire:click.prevent="sortBy('name')">Nama Item @if($sortField==='name')<i class="bi bi-caret-{{ $sortDir==='asc'?'up':'down' }}-fill"></i>@endif</a></th>
                                            <th class="text-end"><a href="#" class="text-decoration-none text-dark fw-bold" wire:click.prevent="sortBy('quantity')">Jumlah @if($sortField==='quantity')<i class="bi bi-caret-{{ $sortDir==='asc'?'up':'down' }}-fill"></i>@endif</a></th>
                                            <th class="d-none d-sm-table-cell"><a href="#" class="text-decoration-none text-dark fw-bold" wire:click.prevent="sortBy('unit')">Satuan @if($sortField==='unit')<i class="bi bi-caret-{{ $sortDir==='asc'?'up':'down' }}-fill"></i>@endif</a></th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rows as $it)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $it->name }}</div>
                                                <div class="d-sm-none small text-muted">Jumlah: {{ $it->quantity }}{{ $it->unit ? ' ' . $it->unit : '' }}</div>
                                            </td>
                                            <td class="text-end">{{ $it->quantity }}</td>
                                            <td class="d-none d-sm-table-cell">{{ $it->unit ?: '-' }}</td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm flex-wrap">
                                                    <button class="btn btn-outline-primary" wire:click="edit({{ $it->id }})"><i class="bi bi-pencil"></i></button>
                                                    <button class="btn btn-outline-danger" x-data @click="$wire.dispatch('swal:confirm', { method: 'destroy', id: {{ $it->id }}, title: 'Hapus Item Ini?', text: 'Item \'{{ addslashes($it->name) }}\' akan dihapus.' })"><i class="bi bi-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center p-4 text-muted">Belum ada item inventori di kamar ini.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile Card List -->
                            <div class="d-sm-none">
                                <div class="vstack gap-2">
                                    @forelse($rows as $it)
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div class="fw-semibold">{{ $it->name }}</div>
                                                    <span class="badge bg-light text-dark">{{ $it->quantity }}{{ $it->unit ? ' ' . $it->unit : '' }}</span>
                                                </div>
                                                <div class="mt-2 d-flex gap-2">
                                                    <button class="btn btn-sm btn-outline-primary" wire:click="edit({{ $it->id }})"><i class="bi bi-pencil"></i> Ubah</button>
                                                    <button class="btn btn-sm btn-outline-danger" x-data @click="$wire.dispatch('swal:confirm', { method: 'destroy', id: {{ $it->id }}, title: 'Hapus Item Ini?', text: 'Item \'{{ addslashes($it->name) }}\' akan dihapus.' })"><i class="bi bi-trash"></i> Hapus</button>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted">Belum ada item inventori di kamar ini.</div>
                                    @endforelse
                                </div>
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
                                        <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="bi bi-clipboard-plus me-2"></i>Salin Inventori Antar Kamar</h6></div>
                                        <div class="card-body">
                                            <div class="mb-3"><label class="form-label">Salin dari Kamar (Sumber)</label><select class="form-select" wire:model.live="sourceRoomId"><option value="">-- pilih sumber --</option>@foreach($rooms as $r)<option value="{{ $r->id }}">No. {{ $r->room_number }}</option>@endforeach</select></div>
                                            <div class="mb-3"><label class="form-label">Ke Kamar (Tujuan)</label><select class="form-select" wire:model="targetRoomId"><option value="">-- pilih tujuan --</option>@foreach($rooms as $r)<option value="{{ $r->id }}" @disabled($r->id == $sourceRoomId)>No. {{ $r->room_number }}</option>@endforeach</select></div>
                                            <div class="form-check mb-1"><input class="form-check-input" type="checkbox" id="overwrite" wire:model.live="overwrite"><label class="form-check-label" for="overwrite">Timpa item di kamar tujuan</label></div>
                                            <small class="form-text text-muted d-block mb-3">Jika dicentang, semua item di tujuan akan dihapus sebelum item baru disalin.</small>
                                            <div class="d-grid"><button class="btn btn-info" @disabled(!$sourceRoomId || !$targetRoomId) x-data @click="$wire.dispatch('swal:confirm', { method: 'inventory:copy', title: 'Salin Item?', text: '{{ $overwrite ? 'Item tujuan akan ditimpa.' : 'Salin komposisi item kamar.' }}', confirmText: 'Ya, salin' })"><i class="bi bi-files me-1"></i> Salin ke 1 Kamar</button></div>
                                            @if(!$sourceRoomId || !$targetRoomId)
                                                <small class="text-muted d-block mt-2">Pilih kamar sumber dan satu kamar tujuan.</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="bi bi-distribute-vertical me-2"></i>Tindakan ke Banyak Kamar</h6></div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Pilih Kamar Tujuan</label>
                                                <select class="form-select" wire:model.live="targetRoomIds" multiple size="5">
                                                    @foreach($rooms as $r)
                                                        <option value="{{ $r->id }}" @disabled($r->id == $sourceRoomId)>No. {{ $r->room_number }} @if(isset($roomStatus[$r->id]))({{ $roomStatus[$r->id] === 'complete' ? '✓' : '✗' }})@endif</option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">Gunakan Ctrl/Cmd + Klik, atau gunakan pemilih kamar.</small>
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" x-data @click="$wire.showRoomPicker = true; $wire.dispatch('modal:show', { id: 'roomPickerModal' })">
                                                        <i class="bi bi-list-check me-1"></i>Pilih Kamar
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="d-grid gap-2">
                                                 <button class="btn btn-primary" @disabled(!$sourceRoomId || empty($targetRoomIds)) x-data @click="$wire.dispatch('swal:confirm', { method: 'inventory:copyBulk', title: 'Salin ke Banyak Kamar?', text: '{{ $overwrite ? 'Item tujuan akan ditimpa.' : 'Salin item dari sumber ke kamar terpilih.' }}', confirmText: 'Ya, salin' })"><i class="bi bi-clipboard2-plus me-1"></i> Salin dari Sumber ke Terpilih</button>
                                                 <button class="btn btn-success" @disabled(!$roomTypeId || empty($targetRoomIds)) x-data @click="$wire.dispatch('swal:confirm', { method: 'inventory:applyTemplate', title: 'Terapkan Template?', text: 'Terapkan template ke kamar tujuan?{{ $overwrite ? ' (Mode Timpa aktif)' : '' }}', confirmText: 'Ya, terapkan' })"><i class="bi bi-journal-check me-1"></i> Terapkan Template ke Terpilih</button>
                                                 <hr class="my-2">
                                                 <button class="btn btn-danger" @disabled(!$roomId) x-data @click="$wire.dispatch('swal:confirm', { method: 'inventory:clear', title: 'Hapus Semua Item?', text: 'Hapus semua item dari kamar No. {{ $selectedRoomNumber }}? Tindakan ini tidak dapat dibatalkan.', confirmText: 'Ya, hapus' })"><i class="bi bi-trash3 me-1"></i> Kosongkan Kamar No. {{ $selectedRoomNumber }}</button>
                                            </div>
                                            @if(!$sourceRoomId || empty($targetRoomIds))
                                                <small class="text-muted d-block mt-2">Pilih kamar sumber dan beberapa kamar tujuan.</small>
                                            @endif
                                            @if(!$roomTypeId)
                                                <small class="text-muted d-block">Pilih tipe kamar di filter kiri untuk menerapkan template.</small>
                                            @endif
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
                                                        <td class="text-center"><button class="btn btn-sm btn-outline-danger" wire:click="deleteTemplateItem({{ $tpl->id }})"><i class="bi bi-trash"></i></button></td>
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

@if(!empty($roomId))
<!-- Modal Checklist Kamar Tujuan (JS-controlled) -->
@php($pickerRooms = $rooms)
<div class="modal fade" id="roomPickerModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-list-check me-2"></i>Pilih Kamar Tujuan</h5>
        <button type="button" class="btn-close" wire:click="$set('showRoomPicker', false)" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="input-group input-group-sm mb-2">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="search" class="form-control" placeholder="Cari nomor kamar..." wire:model.live="pickerSearch">
        </div>
        <div class="row row-cols-2 row-cols-md-3 g-2">
          @foreach($pickerRooms as $r)
            @if(!$pickerSearch || str_contains((string)$r->room_number, (string)$pickerSearch))
            <div class="col">
              <label class="form-check d-flex align-items-center gap-2 border rounded p-2 m-0">
                <input class="form-check-input" type="checkbox" value="{{ $r->id }}" wire:model="targetRoomIds" @disabled($r->id == $sourceRoomId)>
                <span>No. {{ $r->room_number }}</span>
                @if(isset($roomStatus[$r->id]))
                  <span class="badge ms-auto {{ $roomStatus[$r->id] === 'complete' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $roomStatus[$r->id] === 'complete' ? 'Lengkap' : 'Kurang' }}</span>
                @endif
              </label>
            </div>
            @endif
          @endforeach
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" wire:click="$set('showRoomPicker', false)" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-primary" wire:click="$set('showRoomPicker', false)" data-bs-dismiss="modal">Selesai</button>
      </div>
    </div>
  </div>
</div>
@endif
