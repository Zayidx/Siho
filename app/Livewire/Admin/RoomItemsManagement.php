<?php

namespace App\Livewire\Admin;
use Livewire\Attributes\Layout;

use App\Models\RoomItemInventory;
use App\Models\Rooms;
use App\Models\RoomType;
use App\Models\RoomTypeItemTemplate;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

#[Title('Inventori Barang per Kamar')]
#[Layout('components.layouts.app')]
class RoomItemsManagement extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $roomTypeId = '';
    public $roomId = '';
    public $roomSearch = '';
    public $search = '';

    public $name = '';
    public $quantity = '';
    public $unit = '';
    public $editId = null;
    public $selectedRoomCapacity = null;
    public $selectedRoomNumber = null;
    public $sortField = 'room_id';
    public $sortDir = 'asc';
    public $sourceRoomId = '';
    public $targetRoomId = '';
    public array $targetRoomIds = [];
    public $overwrite = false;
    public $templateName = '';
    public $templateQuantity = '';
    public $templateUnit = '';
    public array $roomStatus = [];
    public $roomsPerPage = 20;

    public function updatingRoomTypeId(){ $this->resetPage(); $this->roomId=''; }
    public function updatingRoomId(){ $this->resetPage(); }
    public function updatingSearch(){ $this->resetPage(); }
    public function updatingRoomSearch(){ $this->resetPage(); }
    public function updatingSortField(){ $this->resetPage(); }
    public function updatingSortDir(){ $this->resetPage(); }

    public function updatedRoomId()
    {
        if ($this->roomId) {
            $room = Rooms::with('roomType')->find($this->roomId);
            $this->selectedRoomNumber = $room?->room_number;
            $this->selectedRoomCapacity = $room?->roomType?->capacity;
        } else {
            $this->selectedRoomNumber = null;
            $this->selectedRoomCapacity = null;
        }
    }

    public function viewRoom($id)
    {
        $this->roomId = (string) $id;
        $this->updatedRoomId();
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
            'Kasur' => ['qty' => max(1, $capacity), 'unit' => 'buah'],
            'Keset' => ['qty' => 1, 'unit' => 'buah'],
            'Shampoo' => ['qty' => max(1, $capacity), 'unit' => 'botol'],
            'Sabun' => ['qty' => max(1, $capacity), 'unit' => 'batang'],
            'Handuk' => ['qty' => max(1, $capacity), 'unit' => 'buah'],
        ];
        if (isset($map[$name])) {
            $this->name = $name;
            $this->quantity = (string) $map[$name]['qty'];
            $this->unit = $map[$name]['unit'];
        }
    }

    public function sortBy($field)
    {
        $allowed = ['room_id','name','quantity','unit'];
        if (!in_array($field, $allowed, true)) return;
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir = 'asc';
        }
    }

    public function copyItems()
    {
        $this->validate([
            'sourceRoomId' => ['required','integer','exists:rooms,id'],
            'targetRoomId' => ['required','integer','different:sourceRoomId','exists:rooms,id'],
        ], [
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
        $this->validate([
            'sourceRoomId' => ['required','integer','exists:rooms,id'],
            'targetRoomIds' => ['array','min:1'],
            'targetRoomIds.*' => ['integer','different:sourceRoomId','exists:rooms,id'],
        ], [
            'targetRoomIds.min' => 'Pilih minimal satu kamar tujuan.',
            'targetRoomIds.*.different' => 'Kamar sumber tidak boleh ada di daftar tujuan.'
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
        $this->validate([
            'roomId' => ['required','integer','exists:rooms,id'],
        ]);
        RoomItemInventory::where('room_id', (int) $this->roomId)->delete();
        $this->dispatch('swal:success', ['message' => 'Semua item pada kamar terpilih telah dihapus.']);
        $this->resetPage();
    }

    #[On('inventory:clear')]
    public function clearCurrentRoomConfirmed()
    {
        $this->clearCurrentRoom();
    }

    protected function messages(): array
    {
        return [
            'roomId.required' => 'Pilih kamar terlebih dahulu.',
            'name.required' => 'Nama item wajib diisi.',
            'quantity.required' => 'Jumlah wajib diisi.',
            'quantity.min' => 'Jumlah minimal 0.',
        ];
    }

    public function add()
    {
        $data = $this->validate([
            'roomId' => ['required','integer','exists:rooms,id'],
            'name' => ['required','string','max:100'],
            'quantity' => ['required','integer','min:0'],
            'unit' => ['nullable','string','max:20'],
        ]);
        RoomItemInventory::create([
            'room_id' => (int)$this->roomId,
            'name' => $this->name,
            'quantity' => (int)$this->quantity,
            'unit' => $this->unit ?: null,
        ]);
        $this->reset(['name','quantity','unit']);
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
        $this->updatedRoomId();
    }

    public function update()
    {
        $this->validate([
            'editId' => ['required','integer','exists:room_item_inventories,id'],
            'roomId' => ['required','integer','exists:rooms,id'],
            'name' => ['required','string','max:100'],
            'quantity' => ['required','integer','min:0'],
            'unit' => ['nullable','string','max:20'],
        ]);
        $row = RoomItemInventory::findOrFail($this->editId);
        $row->update([
            'room_id' => (int)$this->roomId,
            'name' => $this->name,
            'quantity' => (int)$this->quantity,
            'unit' => $this->unit ?: null,
        ]);
        $this->dispatch('swal:success', ['message' => 'Item diperbarui.']);
        $this->cancel();
    }

    public function cancel()
    {
        $this->reset(['editId','name','quantity','unit']);
    }

    public function destroy($id)
    {
        RoomItemInventory::findOrFail($id)->delete();
        $this->dispatch('swal:success', ['message' => 'Item dihapus.']);
    }

    // Template Methods
    public function addTemplateItem()
    {
        $this->validate([
            'roomTypeId' => ['required','integer','exists:room_types,id'],
            'templateName' => ['required','string','max:100'],
            'templateQuantity' => ['required','integer','min:0'],
            'templateUnit' => ['nullable','string','max:20'],
        ]);
        RoomTypeItemTemplate::create([
            'room_type_id' => (int) $this->roomTypeId,
            'name' => $this->templateName,
            'quantity' => (int) $this->templateQuantity,
            'unit' => $this->templateUnit ?: null,
        ]);
        $this->reset(['templateName','templateQuantity','templateUnit']);
        $this->dispatch('swal:success', ['message' => 'Item template ditambahkan.']);
    }

    public function deleteTemplateItem($id)
    {
        $row = RoomTypeItemTemplate::where('room_type_id', (int) $this->roomTypeId)->findOrFail($id);
        $row->delete();
        $this->dispatch('swal:success', ['message' => 'Item template dihapus.']);
    }

    public function applyTemplateToTargets()
    {
        $this->validate([
            'roomTypeId' => ['required','integer','exists:room_types,id'],
            'targetRoomIds' => ['array','min:1'],
            'targetRoomIds.*' => ['integer','exists:rooms,id'],
        ], [
            'targetRoomIds.min' => 'Pilih minimal satu kamar tujuan.',
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
        $types = RoomType::orderBy('name')->get(['id','name']);

        // Mode list kamar (jika belum memilih kamar)
        $roomPage = null;
        if (empty($this->roomId)) {
            $roomPage = Rooms::with('roomType')
                ->when($this->roomTypeId, fn($q)=>$q->where('room_type_id', (int)$this->roomTypeId))
                ->when($this->roomSearch, fn($q)=>$q->where('room_number','like','%'.$this->roomSearch.'%'))
                ->orderBy('room_number')
                ->paginate($this->roomsPerPage);
        }
        // Daftar kamar untuk dropdown (saat kamar dipilih)
        $rooms = Rooms::when($this->roomTypeId, fn($q)=>$q->where('room_type_id', (int)$this->roomTypeId))
            ->when($this->roomSearch, fn($q)=>$q->where('room_number','like','%'.$this->roomSearch.'%'))
            ->orderBy('room_number')
            ->limit(300)
            ->get(['id','room_number']);

        // Compute room status (lengkap/tidak) untuk rooms yang terlihat
        $this->roomStatus = [];
        if (!empty($this->roomId)) {
            // hitung status untuk kumpulan rooms dropdown saat mode item
            $roomIds = $rooms->pluck('id');
            $capacities = Rooms::whereIn('rooms.id', $roomIds)
                ->join('room_types','rooms.room_type_id','=','room_types.id')
                ->pluck('room_types.capacity','rooms.id');
            $items = RoomItemInventory::whereIn('room_id', $roomIds)->get()->groupBy('room_id');
            foreach ($rooms as $room) {
                $cap = (int) ($capacities[$room->id] ?? 1);
                $list = $items->get($room->id, collect());
                $need = 0;
                $need += ((int) ($list->firstWhere('name','Kasur')->quantity ?? 0) < max(1,$cap)) ? 1 : 0;
                $need += ((int) ($list->firstWhere('name','Shampoo')->quantity ?? 0) < max(1,$cap)) ? 1 : 0;
                $need += ((int) ($list->firstWhere('name','Sabun')->quantity ?? 0) < max(1,$cap)) ? 1 : 0;
                $need += ((int) ($list->firstWhere('name','Handuk')->quantity ?? 0) < max(1,$cap)) ? 1 : 0;
                $this->roomStatus[$room->id] = $need === 0 ? 'complete' : 'need';
            }
        } elseif ($roomPage && $roomPage->count()) {
            $roomIds = $roomPage->pluck('id');
            $capacities = Rooms::whereIn('rooms.id', $roomIds)
                ->join('room_types','rooms.room_type_id','=','room_types.id')
                ->pluck('room_types.capacity','rooms.id');
            $items = RoomItemInventory::whereIn('room_id', $roomIds)->get()->groupBy('room_id');
            foreach ($roomPage as $room) {
                $cap = (int) ($capacities[$room->id] ?? 1);
                $list = $items->get($room->id, collect());
                $need = 0;
                $need += ((int) ($list->firstWhere('name','Kasur')->quantity ?? 0) < max(1,$cap)) ? 1 : 0;
                $need += ((int) ($list->firstWhere('name','Shampoo')->quantity ?? 0) < max(1,$cap)) ? 1 : 0;
                $need += ((int) ($list->firstWhere('name','Sabun')->quantity ?? 0) < max(1,$cap)) ? 1 : 0;
                $need += ((int) ($list->firstWhere('name','Handuk')->quantity ?? 0) < max(1,$cap)) ? 1 : 0;
                $this->roomStatus[$room->id] = $need === 0 ? 'complete' : 'need';
            }
        }

        $q = RoomItemInventory::with('room')
            ->when($this->roomId, fn($qq)=>$qq->where('room_id', (int)$this->roomId))
            ->when(!$this->roomId && $this->roomTypeId, function($qq){
                $roomIds = Rooms::where('room_type_id', (int)$this->roomTypeId)->pluck('id');
                $qq->whereIn('room_id', $roomIds);
            })
            ->when($this->search, fn($qq)=>$qq->where('name','like','%'.$this->search.'%'))
            ->when(true, function($qq){
                $field = in_array($this->sortField, ['room_id','name','quantity','unit'], true) ? $this->sortField : 'room_id';
                $dir = $this->sortDir === 'desc' ? 'desc' : 'asc';
                if ($field === 'room_id') {
                    $qq->join('rooms', 'room_item_inventories.room_id', '=', 'rooms.id')
                       ->orderBy('rooms.room_number', $dir)
                       ->select('room_item_inventories.*');
                } else {
                    $qq->orderBy($field, $dir);
                }
                $qq->orderBy('room_item_inventories.id');
            });

        $templates = [];
        if ($this->roomTypeId) {
            $templates = RoomTypeItemTemplate::where('room_type_id', (int)$this->roomTypeId)->orderBy('name')->get();
        }
        
        return view('livewire.admin.room-items-management', [
            'types' => $types,
            'rooms' => $rooms,
            'roomPage' => $roomPage,
            'rows' => $q->paginate(20),
            'templates' => $templates,
        ]);
    }
}
