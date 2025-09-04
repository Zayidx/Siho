<?php

namespace App\Livewire\Fnb;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

#[Layout('components.layouts.app')]
#[Title('Kelola Menu F&B')]
class MenuManagement extends Component
{
    use WithFileUploads, WithPagination;
    protected $paginationTheme = 'bootstrap';
    
    public $categories; 
    public $catSearch = '';

    // Category form
    public $catId, $catName = '', $catDesc = '', $catActive = true, $catImage = null, $catImageExisting = null;

    // Item form
    public $itemId, $itemCategoryId = '', $itemName = '', $itemDesc = '', $itemPrice = 0, $itemActive = true, $itemPopular = false, $itemImage, $itemImageExisting = null;
    // Modal flags (follow room-type-management style)
    public $isOpen = false; // for item modal
    public $showCategoryModal = false;
    public $deleteId = null;

    public function mount(): void
    {
        $this->reloadData();
    }

    private function reloadData(): void
    {
        $this->categories = MenuCategory::query()
            ->when($this->catSearch !== '', fn($q) => $q->where('name','like','%'.trim($this->catSearch).'%'))
            ->orderBy('name')->get();
    }

    public function saveCategory(): void
    {
        $this->validate([
            'catName' => 'required|string|max:120',
        ]);
        $data = [
            'name' => $this->catName,
            'description' => $this->catDesc ?: null,
            'is_active' => (bool)$this->catActive,
        ];
        if ($this->catImage) {
            if ($this->catId && $this->catImageExisting && !str_starts_with($this->catImageExisting, 'http')) {
                Storage::disk('public')->delete($this->catImageExisting);
            }
            $data['image'] = $this->catImage->store('menu/categories', 'public');
        } elseif ($this->catId && $this->catImageExisting) {
            $data['image'] = $this->catImageExisting;
        }

        MenuCategory::updateOrCreate(['id' => $this->catId], $data);
        $this->reset(['catId','catName','catDesc','catActive','catImage','catImageExisting']);
        $this->catActive = true;
        $this->reloadData();
        $this->showCategoryModal = false;
        $this->dispatch('swal:success', ['message' => 'Kategori disimpan.']);
    }

    public function editCategory(int $id): void
    {
        $c = MenuCategory::findOrFail($id);
        $this->catId = $c->id;
        $this->catName = $c->name;
        $this->catDesc = $c->description;
        $this->catActive = $c->is_active;
        $this->catImageExisting = $c->image;
        $this->showCategoryModal = true;
    }

    public function openCreateCategory(): void
    {
        $this->reset(['catId','catName','catDesc','catImage','catImageExisting']);
        $this->catActive = true;
        $this->showCategoryModal = true;
    }

    public function closeCategoryModal(): void
    {
        $this->showCategoryModal = false;
    }

    public function deleteCategory(int $id): void
    {
        $this->deleteId = $id;
        $this->dispatch('swal:confirm', [
            'method' => 'fnbDeleteCategoryConfirmed',
            'id' => $id,
            'title' => 'Hapus Kategori?',
            'text' => 'Kategori akan dihapus. Item menu yang terkait perlu diperiksa.',
        ]);
    }

    protected $listeners = [
        'fnbDeleteConfirmed' => 'performDeleteItem',
        'fnbDeleteCategoryConfirmed' => 'performDeleteCategory',
    ];

    public function saveItem(): void
    {
        $this->validate([
            'itemCategoryId' => 'required|exists:menu_categories,id',
            'itemName' => 'required|string|max:160',
            'itemPrice' => 'required|integer|min:0',
        ]);

        $data = [
            'menu_category_id' => $this->itemCategoryId,
            'name' => $this->itemName,
            'description' => $this->itemDesc ?: null,
            'price' => (int) $this->itemPrice,
            'is_active' => (bool)$this->itemActive,
            'is_popular' => (bool)$this->itemPopular,
        ];
        if ($this->itemImage) {
            if ($this->itemId && $this->itemImageExisting && !str_starts_with($this->itemImageExisting, 'http')) {
                Storage::disk('public')->delete($this->itemImageExisting);
            }
            $data['image'] = $this->itemImage->store('menu', 'public');
        } elseif ($this->itemId && $this->itemImageExisting) {
            // keep existing image path
            $data['image'] = $this->itemImageExisting;
        }
        MenuItem::updateOrCreate(['id' => $this->itemId], $data);
        $this->reset(['itemId','itemCategoryId','itemName','itemDesc','itemPrice','itemActive','itemPopular','itemImage']);
        $this->itemImageExisting = null;
        $this->itemActive = true; $this->itemPopular = false;
        $this->isOpen = false;
        $this->reloadData();
    }

    public function editItem(int $id): void
    {
        $it = MenuItem::findOrFail($id);
        $this->itemId = $it->id;
        $this->itemCategoryId = $it->menu_category_id;
        $this->itemName = $it->name;
        $this->itemDesc = $it->description;
        $this->itemPrice = $it->price;
        $this->itemActive = $it->is_active;
        $this->itemPopular = $it->is_popular;
        $this->itemImageExisting = $it->image;
        $this->isOpen = true;
    }

    public function deleteItem(int $id): void
    {
        $this->deleteId = $id;
        $this->dispatch('swal:confirm', [
            'method' => 'fnbDeleteConfirmed',
            'id' => $id,
            'title' => 'Hapus Item Menu?',
            'text' => 'Item akan dihapus dan tidak bisa dikembalikan',
        ]);
    }

    public function performDeleteItem($payload = null): void
    {
        $id = is_array($payload) && isset($payload['id']) ? (int)$payload['id'] : (int)($this->deleteId ?: 0);
        if ($id) {
            $it = MenuItem::findOrFail($id);
            if ($it->image && !str_starts_with($it->image, 'http')) {
                Storage::disk('public')->delete($it->image);
            }
            $it->delete();
            $this->dispatch('swal:success', ['message' => 'Item menu dihapus.']);
            $this->reloadData();
        }
    }

    public function performDeleteCategory($payload = null): void
    {
        $id = is_array($payload) && isset($payload['id']) ? (int)$payload['id'] : (int)($this->deleteId ?: 0);
        if ($id) {
            $c = MenuCategory::findOrFail($id);
            if ($c->image && !str_starts_with($c->image, 'http')) {
                Storage::disk('public')->delete($c->image);
            }
            $c->delete();
            $this->dispatch('swal:success', ['message' => 'Kategori dihapus.']);
            $this->reloadData();
        }
    }

    public function openCreateItem(): void
    {
        $this->reset(['itemId','itemCategoryId','itemName','itemDesc','itemPrice','itemActive','itemPopular','itemImage']);
        $this->itemImageExisting = null;
        $this->itemActive = true; $this->itemPopular = false;
        $this->isOpen = true;
    }

    public function closeItemModal(): void
    {
        $this->isOpen = false;
    }

    // Compatibility with room-type-management naming
    public function create(): void { $this->openCreateItem(); }
    public function openModal(): void { $this->isOpen = true; }
    public function closeModal(): void { $this->isOpen = false; }
    public function store(): void { $this->saveItem(); }

    public function render()
    {
        $items = MenuItem::with('category')->latest()->paginate(10);
        return view('livewire.fnb.menu-management', compact('items'));
    }
}
