<?php

namespace App\Livewire\Admin;

use App\Models\Room;
use App\Models\RoomItemInventory;
use App\Models\RoomType;
use App\Models\RoomTypeItemTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Inventori Barang per Kamar')]
#[Layout('components.layouts.app')]
class RoomItemsManagement extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url]
    public $roomTypeId = '';

    #[Url]
    public $roomId = '';

    #[Url]
    public $roomSearch = '';

    #[Url]
    public $search = '';

    public $name = '';

    public $quantity = '';

    public $unit = '';

    public $editId = null;

    public $selectedRoomCapacity = null;

    public $selectedRoomNumber = null;

    #[Url]
    public $sortField = 'name'; // Default sort by name

    #[Url]
    public $sortDir = 'asc';

    public $sourceRoomId = '';

    public $targetRoomId = '';

    public array $targetRoomIds = [];

    public $overwrite = false;

    public $templateName = '';

    public $templateQuantity = '';

    public $templateUnit = '';

    // public array $roomStatus = [];
    public $roomsPerPage = 20;

    public $showRoomPicker = false;

    public $pickerSearch = '';

    public array $unitsOptions = ['buah', 'botol', 'batang', 'pcs', 'unit', 'lembar', 'set', 'pasang'];

    // CSV import/export removed as requested
    public array $undoBuffer = [];

    public bool $showUndoBar = false;

    public bool $canEdit = true;

    // Lifecycle hooks for resetting pagination
    public function updatingRoomTypeId()
    {
        $this->resetPage();
        $this->roomId = '';
    }

    public function updatingRoomId()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoomSearch()
    {
        $this->resetPage();
    }

    public function updatingSortField()
    {
        $this->resetPage();
    }

    public function updatingSortDir()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->canEdit = (Auth::user()?->role?->name === 'superadmin');
        if ($this->roomId) {
            $this->updatedRoomId();
        }
    }

    public function updatedRoomId()
    {
        if ($this->roomId) {
            $room = Room::with('roomType')->find($this->roomId);
            $this->selectedRoomNumber = $room?->room_number;
            $this->selectedRoomCapacity = $room?->roomType?->capacity;
        } else {
            $this->selectedRoomNumber = null;
            $this->selectedRoomCapacity = null;
        }
    }

    public function viewRoom($id)
    {
        Log::info('RoomItemsManagement viewRoom', ['room_id' => $id, 'user_id' => auth()->id()]);
        $this->roomId = (string) $id;
        $this->updatedRoomId();
        $this->reset(['editId', 'name', 'quantity', 'unit', 'search']);
        $this->resetPage();
    }

    public function backToRooms()
    {
        $this->roomId = '';
        $this->resetPage();
    }

    public function fillPreset($name)
    {
        $name = (string) $name;
        $capacity = (int) ($this->selectedRoomCapacity ?? 1);
        $map = [
            'Kasur' => ['qty' => max(1, $capacity), 'unit' => 'unit'],
            'Keset' => ['qty' => 1, 'unit' => 'lembar'],
            'Shampoo' => ['qty' => max(1, $capacity), 'unit' => 'botol'],
            'Sabun' => ['qty' => max(1, $capacity), 'unit' => 'batang'],
            'Handuk' => ['qty' => max(1, $capacity), 'unit' => 'lembar'],
        ];
        if (isset($map[$name])) {
            $this->name = $name;
            $this->quantity = (string) $map[$name]['qty'];
            $this->unit = $map[$name]['unit'];
        }
    }

    public function sortBy($field)
    {
        $allowed = ['room_id', 'name', 'quantity', 'unit'];
        if (! in_array($field, $allowed, true)) {
            return;
        }
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir = 'asc';
        }
    }

    public function copyItems()
    {
        Log::info('RoomItemsManagement copyItems start', ['source' => $this->sourceRoomId, 'target' => $this->targetRoomId, 'overwrite' => $this->overwrite]);
        $this->validate([
            'sourceRoomId' => ['required', 'integer', 'exists:rooms,id'],
            'targetRoomId' => ['required', 'integer', 'different:sourceRoomId', 'exists:rooms,id'],
        ], [
            'sourceRoomId.required' => 'Pilih kamar sumber.',
            'sourceRoomId.exists' => 'Kamar sumber tidak ditemukan.',
            'targetRoomId.required' => 'Pilih kamar tujuan.',
            'targetRoomId.exists' => 'Kamar tujuan tidak ditemukan.',
            'targetRoomId.different' => 'Sumber dan tujuan tidak boleh sama.',
        ]);

        $sourceItems = RoomItemInventory::where('room_id', (int) $this->sourceRoomId)->get();
        if ($sourceItems->isEmpty()) {
            $this->dispatch('swal:info', ['message' => 'Kamar sumber tidak memiliki item.']);

            return;
        }
        if ($this->overwrite) {
            RoomItemInventory::where('room_id', (int) $this->targetRoomId)->delete();
        }
        foreach ($sourceItems as $it) {
            RoomItemInventory::updateOrCreate(
                ['room_id' => (int) $this->targetRoomId, 'name' => $it->name],
                ['quantity' => $it->quantity, 'unit' => $it->unit]
            );
        }
        Log::info('RoomItemsManagement copyItems success');
        $this->dispatch('swal:success', ['message' => 'Item berhasil disalin ke kamar tujuan.']);
        $this->resetPage();
    }

    #[On('inventory:copy')]
    public function copyItemsConfirmed()
    {
        $this->copyItems();
    }

    public function copyItemsBulk()
    {
        Log::info('RoomItemsManagement copyItemsBulk start', ['source' => $this->sourceRoomId, 'targets' => $this->targetRoomIds, 'overwrite' => $this->overwrite]);
        $this->validate([
            'sourceRoomId' => ['required', 'integer', 'exists:rooms,id'],
            'targetRoomIds' => ['array', 'min:1'],
            'targetRoomIds.*' => ['integer', 'different:sourceRoomId', 'exists:rooms,id'],
        ], [
            'sourceRoomId.required' => 'Pilih kamar sumber.',
            'sourceRoomId.exists' => 'Kamar sumber tidak ditemukan.',
            'targetRoomIds.min' => 'Pilih minimal satu kamar tujuan.',
            'targetRoomIds.*.different' => 'Kamar sumber tidak boleh ada di daftar tujuan.',
            'targetRoomIds.*.exists' => 'Kamar tujuan tidak ditemukan.',
        ]);

        $sourceItems = RoomItemInventory::where('room_id', (int) $this->sourceRoomId)->get();
        if ($sourceItems->isEmpty()) {
            $this->dispatch('swal:info', ['message' => 'Kamar sumber tidak memiliki item.']);

            return;
        }
        foreach ($this->targetRoomIds as $tid) {
            if ($this->overwrite) {
                RoomItemInventory::where('room_id', (int) $tid)->delete();
            }
            foreach ($sourceItems as $it) {
                RoomItemInventory::updateOrCreate(
                    ['room_id' => (int) $tid, 'name' => $it->name],
                    ['quantity' => $it->quantity, 'unit' => $it->unit]
                );
            }
        }
        Log::info('RoomItemsManagement copyItemsBulk success', ['count' => count($this->targetRoomIds)]);
        $this->dispatch('swal:success', ['message' => 'Item berhasil disalin ke kamar-kamar tujuan.']);
        $this->reset(['targetRoomIds']);
        $this->resetPage();
    }

    #[On('inventory:copyBulk')]
    public function copyItemsBulkConfirmed()
    {
        $this->copyItemsBulk();
    }

    public function clearCurrentRoom()
    {
        Log::warning('RoomItemsManagement clearCurrentRoom', ['roomId' => $this->roomId]);
        $this->validate([
            'roomId' => ['required', 'integer', 'exists:rooms,id'],
        ]);
        $backup = RoomItemInventory::where('room_id', (int) $this->roomId)->get(['name', 'quantity', 'unit'])->toArray();
        RoomItemInventory::where('room_id', (int) $this->roomId)->delete();
        $this->undoBuffer = ['type' => 'clear_room', 'room_id' => (int) $this->roomId, 'data' => $backup];
        $this->showUndoBar = true;
        $this->dispatch('swal:success', ['message' => 'Semua item pada kamar terpilih telah dihapus. Klik Undo untuk membatalkan.']);
        $this->resetPage();
    }

    #[On('inventory:clear')]
    public function clearCurrentRoomConfirmed()
    {
        $this->clearCurrentRoom();
    }

    public function undoClear(): void
    {
        if (($this->undoBuffer['type'] ?? '') !== 'clear_room') {
            return;
        }
        $rid = (int) ($this->undoBuffer['room_id'] ?? 0);
        $rows = (array) ($this->undoBuffer['data'] ?? []);
        if ($rid > 0 && ! empty($rows)) {
            foreach ($rows as $row) {
                RoomItemInventory::create([
                    'room_id' => $rid,
                    'name' => (string) ($row['name'] ?? ''),
                    'quantity' => (int) ($row['quantity'] ?? 0),
                    'unit' => $row['unit'] ?? null,
                ]);
            }
            $this->dispatch('swal:success', ['message' => 'Penghapusan dibatalkan. Item dikembalikan.']);
        }
        $this->undoBuffer = [];
        $this->showUndoBar = false;
        $this->resetPage();
    }

    public function selectAllFiltered(): void
    {
        $ids = Room::when($this->roomTypeId, fn ($q) => $q->where('room_type_id', (int) $this->roomTypeId))
            ->when($this->roomSearch, fn ($q) => $q->where('room_number', 'like', '%'.$this->roomSearch.'%'))
            ->orderBy('room_number')
            ->limit(1000)
            ->pluck('id')
            ->toArray();
        $this->targetRoomIds = $ids;
    }

    // CSV export/import removed by request

    protected function messages(): array
    {
        return [
            'roomId.required' => 'Pilih kamar terlebih dahulu.',
            'name.required' => 'Nama item wajib diisi.',
            'quantity.required' => 'Jumlah wajib diisi.',
            'quantity.min' => 'Jumlah minimal 0.',
        ];
    }

    protected $validationAttributes = [
        'roomId' => 'Kamar',
        'sourceRoomId' => 'Kamar sumber',
        'targetRoomId' => 'Kamar tujuan',
        'targetRoomIds' => 'Daftar kamar tujuan',
        'targetRoomIds.*' => 'Kamar tujuan',
        'name' => 'Nama item',
        'quantity' => 'Jumlah',
        'unit' => 'Satuan',
        'roomTypeId' => 'Tipe kamar',
        'templateName' => 'Nama item template',
        'templateQuantity' => 'Jumlah item template',
        'templateUnit' => 'Satuan item template',
    ];

    public function add()
    {
        Log::info('RoomItemsManagement add start', ['roomId' => $this->roomId, 'name' => $this->name]);
        $data = $this->validate([
            'roomId' => ['required', 'integer', 'exists:rooms,id'],
            'name' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:20'],
        ]);
        RoomItemInventory::create([
            'room_id' => (int) $this->roomId,
            'name' => $this->name,
            'quantity' => (int) $this->quantity,
            'unit' => $this->unit ?: null,
        ]);
        Log::info('RoomItemsManagement add success');
        $this->reset(['name', 'quantity', 'unit']);
        $this->dispatch('swal:success', ['message' => 'Item ditambahkan.']);
        $this->resetPage();
    }

    public function edit($id)
    {
        $row = RoomItemInventory::findOrFail($id);
        $this->editId = $row->id;
        $this->name = $row->name;
        $this->quantity = (string) $row->quantity;
        $this->unit = (string) ($row->unit ?? '');
        $this->roomId = (string) $row->room_id;
        // $this->updatedRoomId(); // not needed, already in the same room
    }

    public function update()
    {
        Log::info('RoomItemsManagement update start', ['editId' => $this->editId]);
        $this->validate([
            'editId' => ['required', 'integer', 'exists:room_item_inventories,id'],
            'roomId' => ['required', 'integer', 'exists:rooms,id'],
            'name' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:20'],
        ]);
        $row = RoomItemInventory::findOrFail($this->editId);
        $row->update([
            // 'room_id' => (int)$this->roomId, // no need to update room_id on item edit
            'name' => $this->name,
            'quantity' => (int) $this->quantity,
            'unit' => $this->unit ?: null,
        ]);
        Log::info('RoomItemsManagement update success');
        $this->dispatch('swal:success', ['message' => 'Item diperbarui.']);
        $this->cancel();
    }

    public function cancel()
    {
        $this->reset(['editId', 'name', 'quantity', 'unit']);
    }

    #[On('destroy')]
    public function destroy($id)
    {
        Log::warning('RoomItemsManagement destroy', ['id' => $id]);
        RoomItemInventory::findOrFail($id)->delete();
        $this->dispatch('swal:success', ['message' => 'Item dihapus.']);
    }

    // Inline update helpers for a more user-friendly table
    public function increment($id)
    {
        $row = RoomItemInventory::findOrFail((int) $id);
        $row->quantity = (int) $row->quantity + 1;
        $row->save();
        // $this->dispatch('swal:info', ['message' => 'Jumlah ditambah.']); // Can be noisy
    }

    public function decrement($id)
    {
        $row = RoomItemInventory::findOrFail((int) $id);
        $qty = max(0, (int) $row->quantity - 1);
        $row->quantity = $qty;
        $row->save();
        // $this->dispatch('swal:info', ['message' => 'Jumlah dikurangi.']); // Can be noisy
    }

    public function updateQuantity($id, $qty)
    {
        $qty = (int) $qty;
        if ($qty < 0) {
            $qty = 0;
        }
        $row = RoomItemInventory::findOrFail((int) $id);
        $row->quantity = $qty;
        $row->save();
        $this->dispatch('swal:success', ['message' => 'Jumlah disimpan.']);
    }

    public function updateUnit($id, $unit)
    {
        $unit = trim((string) $unit);
        if ($unit === '') {
            $unit = null;
        }
        $row = RoomItemInventory::findOrFail((int) $id);
        $row->unit = $unit;
        $row->save();
        $this->dispatch('swal:success', ['message' => 'Satuan diperbarui.']);
    }

    // Template Methods
    public function addTemplateItem()
    {
        Log::info('RoomItemsManagement addTemplateItem', ['roomTypeId' => $this->roomTypeId, 'name' => $this->templateName]);
        $this->validate([
            'roomTypeId' => ['required', 'integer', 'exists:room_types,id'],
            'templateName' => ['required', 'string', 'max:100'],
            'templateQuantity' => ['required', 'integer', 'min:0'],
            'templateUnit' => ['nullable', 'string', 'max:20'],
        ], [
            'roomTypeId.required' => 'Pilih tipe kamar.',
            'roomTypeId.exists' => 'Tipe kamar tidak ditemukan.',
            'templateName.required' => 'Nama item template wajib diisi.',
            'templateName.max' => 'Nama item template terlalu panjang.',
            'templateQuantity.required' => 'Jumlah wajib diisi.',
            'templateQuantity.integer' => 'Jumlah harus berupa angka.',
            'templateQuantity.min' => 'Jumlah minimal 0.',
            'templateUnit.max' => 'Satuan terlalu panjang.',
        ]);
        RoomTypeItemTemplate::create([
            'room_type_id' => (int) $this->roomTypeId,
            'name' => $this->templateName,
            'quantity' => (int) $this->templateQuantity,
            'unit' => $this->templateUnit ?: null,
        ]);
        $this->reset(['templateName', 'templateQuantity', 'templateUnit']);
        $this->dispatch('swal:success', ['message' => 'Item template ditambahkan.']);
    }

    public function deleteTemplateItem($id)
    {
        Log::warning('RoomItemsManagement deleteTemplateItem', ['id' => $id, 'roomTypeId' => $this->roomTypeId]);
        $row = RoomTypeItemTemplate::where('room_type_id', (int) $this->roomTypeId)->findOrFail($id);
        $row->delete();
        $this->dispatch('swal:success', ['message' => 'Item template dihapus.']);
    }

    public function applyTemplateToTargets()
    {
        Log::info('RoomItemsManagement applyTemplateToTargets start', ['roomTypeId' => $this->roomTypeId, 'targets' => $this->targetRoomIds, 'overwrite' => $this->overwrite]);
        $this->validate([
            'roomTypeId' => ['required', 'integer', 'exists:room_types,id'],
            'targetRoomIds' => ['array', 'min:1'],
            'targetRoomIds.*' => ['integer', 'exists:rooms,id'],
        ], [
            'roomTypeId.required' => 'Pilih tipe kamar.',
            'roomTypeId.exists' => 'Tipe kamar tidak ditemukan.',
            'targetRoomIds.min' => 'Pilih minimal satu kamar tujuan.',
            'targetRoomIds.*.exists' => 'Kamar tujuan tidak ditemukan.',
        ]);
        $tpl = RoomTypeItemTemplate::where('room_type_id', (int) $this->roomTypeId)->get();
        if ($tpl->isEmpty()) {
            $this->dispatch('swal:info', ['message' => 'Template untuk tipe kamar ini kosong.']);

            return;
        }
        foreach ($this->targetRoomIds as $tid) {
            if ($this->overwrite) {
                RoomItemInventory::where('room_id', (int) $tid)->delete();
            }
            foreach ($tpl as $it) {
                RoomItemInventory::updateOrCreate(
                    ['room_id' => (int) $tid, 'name' => $it->name],
                    ['quantity' => $it->quantity, 'unit' => $it->unit]
                );
            }
        }
        Log::info('RoomItemsManagement applyTemplateToTargets success', ['count' => count($this->targetRoomIds)]);
        $this->dispatch('swal:success', ['message' => 'Template berhasil diterapkan ke kamar-kamar tujuan.']);
        $this->reset(['targetRoomIds']);
        $this->resetPage();
    }

    #[On('inventory:applyTemplate')]
    public function applyTemplateToTargetsConfirmed()
    {
        $this->applyTemplateToTargets();
    }

    public function render()
    {
        $types = RoomType::orderBy('name')->get(['id', 'name']);

        // Main rooms query for various dropdowns and pickers
        $roomsQuery = Room::query()
            ->when($this->roomTypeId, fn ($q) => $q->where('room_type_id', (int) $this->roomTypeId))
            ->when($this->roomSearch, fn ($q) => $q->where('room_number', 'like', '%'.$this->roomSearch.'%'))
            ->orderBy('room_number');

        // Paginated room list for the master view (left column)
        // Always show the left list (highlight selected room instead of hiding the list)
        $roomPage = $roomsQuery->with('roomType')->paginate($this->roomsPerPage);

        // Limited room list for dropdowns (to avoid loading too much data)
        $roomsForDropdowns = (clone $roomsQuery)->limit(300)->get(['id', 'room_number']);

        // Calculate room completion status only for visible rooms
        // completeness badges removed

        $itemQuery = RoomItemInventory::query();
        if ($this->roomId) {
            $itemQuery->where('room_id', (int) $this->roomId);
        }

        $itemQuery->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'));

        // Sorting logic
        $field = in_array($this->sortField, ['name', 'quantity', 'unit'], true) ? $this->sortField : 'name';
        $dir = $this->sortDir === 'desc' ? 'desc' : 'asc';
        $itemQuery->orderBy($field, $dir)->orderBy('id', 'asc');

        $templates = [];
        if ($this->roomTypeId) {
            $templates = RoomTypeItemTemplate::where('room_type_id', (int) $this->roomTypeId)->orderBy('name')->get();
        }

        return view('livewire.admin.room-items-management', [
            'types' => $types,
            'rooms' => $roomsForDropdowns, // Used for dropdowns and picker
            'roomPage' => $roomPage, // Used for the main list
            'rows' => $itemQuery->paginate(20),
            'templates' => $templates,
        ]);
    }
}
