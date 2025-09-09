<?php

namespace App\Livewire\Fnb;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use App\Support\Uploads\Uploader;

#[Layout('components.layouts.app')]
#[Title('Kelola Menu F&B')]
class MenuManagement extends Component
{
    use WithFileUploads, WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Properti untuk Kategori
    public $categories;
    public $catSearch = '';
    public $showCategoryModal = false; // used for state and wire:key
    public $catId, $catName, $catDesc, $catImage, $catImageExisting;
    public $catActive = true;

    // Properti untuk Item Menu
    #[Url(as: 'kategori', keep: true)]
    public $selectedCategoryId = null;

    public $showItemModal = false; // used for state and wire:key
    public $itemId, $itemName, $itemDesc, $itemImage, $itemImageExisting;
    public $itemPrice = '';
    public $itemActive = true;
    public $itemPopular = false;

    // Kontrol untuk daftar Item
    #[Url]
    public $itemSearch = '';
    #[Url]
    public $itemSort = 'name';
    #[Url]
    public $itemDir = 'asc';
    public $perPage = 10;
    
    protected $listeners = [
        'fnbDeleteConfirmed' => 'performDeleteItem',
        'fnbDeleteCategoryConfirmed' => 'performDeleteCategory',
    ];

    public function mount()
    {
        Log::debug('MenuManagement mount', ['user_id' => auth()->id()]);
        $this->loadCategories();
    }
    
    public function loadCategories()
    {
        Log::debug('MenuManagement loadCategories', ['catSearch' => $this->catSearch]);
        $this->categories = MenuCategory::query()
            ->when($this->catSearch, fn($q) => $q->where('name', 'like', '%' . $this->catSearch . '%'))
            ->orderBy('name')
            ->get();
    }
    
    public function updatedCatSearch()
    {
        $this->loadCategories();
    }

    /**
     * =================================================================
     * METODE UNTUK KATEGORI MENU
     * =================================================================
     */

    public function selectCategory($id)
    {
        Log::info('MenuManagement selectCategory', ['category_id' => $id, 'user_id' => auth()->id()]);
        $this->selectedCategoryId = $id;
        $this->resetPage();
        $this->resetItemForm();
    }

    public function openCreateCategory()
    {
        Log::debug('MenuManagement openCreateCategory');
        $this->reset(['catId', 'catName', 'catDesc', 'catImage', 'catImageExisting']);
        $this->catActive = true;
        // Show modal via JS
        $this->dispatch('modal:show', id: 'categoryModal');
    }
    
    public function editCategory(MenuCategory $category)
    {
        Log::debug('MenuManagement editCategory', ['category_id' => $category->id]);
        $this->catId = $category->id;
        $this->catName = $category->name;
        $this->catDesc = $category->description;
        $this->catActive = $category->is_active;
        $this->catImageExisting = $category->image;
        $this->catImage = null;
        $this->dispatch('modal:show', id: 'categoryModal');
    }

    public function saveCategory()
    {
        Log::info('MenuManagement saveCategory start', ['catId' => $this->catId, 'name' => $this->catName]);
        $this->validate([
            'catName' => 'required|string|max:120',
            'catImage' => 'nullable|mimes:jpg,jpeg,png,webp|max:1024',
        ], [
            'catName.required' => 'Nama kategori wajib diisi.',
            'catName.max' => 'Nama kategori terlalu panjang.',
            'catImage.mimes' => 'Format gambar harus jpg, jpeg, png, atau webp.',
            'catImage.max' => 'Ukuran gambar maksimal 1MB.',
        ]);

        $data = [
            'name' => $this->catName,
            'description' => $this->catDesc,
            'is_active' => $this->catActive,
        ];

        if ($this->catImage) {
            Uploader::deletePublicIfLocal($this->catImageExisting);
            $data['image'] = Uploader::storePublicImage($this->catImage, 'menu/categories');
        }

        $saved = MenuCategory::updateOrCreate(['id' => $this->catId], $data);
        Log::info('MenuManagement saveCategory success', ['category_id' => $saved->id]);

        $this->closeCategoryModal();
        $this->loadCategories();
        $this->dispatch('swal:success', ['message' => 'Kategori berhasil disimpan.']);
    }

    public function deleteCategory(MenuCategory $category)
    {
        Log::warning('MenuManagement deleteCategory requested', ['category_id' => $category->id]);
        if ($category->menuItems()->count() > 0) {
            $this->dispatch('swal:error', ['message' => 'Kategori tidak bisa dihapus karena masih memiliki item menu.']);
            return;
        }
        
        $this->dispatch('swal:confirm', [
            'method' => 'fnbDeleteCategoryConfirmed',
            'id' => $category->id,
            'title' => 'Anda yakin?',
            'text' => "Kategori '{$category->name}' akan dihapus secara permanen.",
        ]);
    }
    
    public function performDeleteCategory($payload)
    {
        $category = MenuCategory::find($payload['id']);
        if($category) {
            Log::warning('MenuManagement performDeleteCategory', ['category_id' => $category->id]);
            \App\Support\Uploads\Uploader::deletePublicIfLocal($category->image);
            $category->delete();
            
            if ($this->selectedCategoryId == $payload['id']) {
                $this->selectedCategoryId = null;
            }

            $this->loadCategories();
            $this->dispatch('swal:success', ['message' => 'Kategori telah dihapus.']);
        }
    }
    
    public function closeCategoryModal()
    {
        // Hide via JS and reset input
        $this->dispatch('modal:hide', id: 'categoryModal');
        $this->catImage = null;
        $this->resetErrorBag();
    }
    
    /**
     * =================================================================
     * METODE UNTUK ITEM MENU
     * =================================================================
     */

    public function openCreateItem()
    {
        Log::debug('MenuManagement openCreateItem', ['selectedCategoryId' => $this->selectedCategoryId]);
        $this->resetItemForm();
        $this->dispatch('modal:show', id: 'itemModal');
    }
    
    public function editItem(MenuItem $item)
    {
        Log::debug('MenuManagement editItem', ['item_id' => $item->id]);
        $this->itemId = $item->id;
        $this->itemName = $item->name;
        $this->itemDesc = $item->description;
        $this->itemPrice = $item->price;
        $this->itemActive = $item->is_active;
        $this->itemPopular = $item->is_popular;
        $this->itemImageExisting = $item->image;
        $this->itemImage = null;
        $this->resetErrorBag();
        $this->dispatch('modal:show', id: 'itemModal');
    }
    
    public function saveItem()
    {
        Log::info('MenuManagement saveItem start', ['itemId' => $this->itemId, 'category' => $this->selectedCategoryId, 'name' => $this->itemName]);
        $this->validate([
            'itemName' => 'required|string|max:160',
            'itemPrice' => 'required|integer|min:0',
            'itemImage' => 'nullable|mimes:jpg,jpeg,png,webp|max:1024',
        ], [
            'itemName.required' => 'Nama item wajib diisi.',
            'itemName.max' => 'Nama item terlalu panjang.',
            'itemPrice.required' => 'Harga wajib diisi.',
            'itemPrice.integer' => 'Harga harus berupa angka bulat.',
            'itemPrice.min' => 'Harga minimal 0.',
            'itemImage.mimes' => 'Format gambar harus jpg, jpeg, png, atau webp.',
            'itemImage.max' => 'Ukuran gambar maksimal 1MB.',
        ]);

        if(!$this->selectedCategoryId) {
            $this->dispatch('swal:error', ['message' => 'Silakan pilih kategori terlebih dahulu.']);
            return;
        }

        $data = [
            'menu_category_id' => $this->selectedCategoryId,
            'name' => $this->itemName,
            'description' => $this->itemDesc,
            'price' => $this->itemPrice,
            'is_active' => $this->itemActive,
            'is_popular' => $this->itemPopular,
        ];

        if ($this->itemImage) {
            Uploader::deletePublicIfLocal($this->itemImageExisting);
            $data['image'] = Uploader::storePublicImage($this->itemImage, 'menu/items');
        }

        $saved = MenuItem::updateOrCreate(['id' => $this->itemId], $data);
        Log::info('MenuManagement saveItem success', ['item_id' => $saved->id]);
        
        $this->dispatch('swal:success', ['message' => 'Item menu berhasil disimpan.']);
        $this->closeItemModal();
    }
    
    public function deleteItem(MenuItem $item)
    {
        Log::warning('MenuManagement deleteItem requested', ['item_id' => $item->id]);
        $this->dispatch('swal:confirm', [
            'method' => 'fnbDeleteConfirmed',
            'id' => $item->id,
            'title' => 'Anda yakin?',
            'text' => "Item '{$item->name}' akan dihapus.",
        ]);
    }
    
    public function performDeleteItem($payload)
    {
        $item = MenuItem::find($payload['id']);
        if ($item) {
            Log::warning('MenuManagement performDeleteItem', ['item_id' => $item->id]);
            Uploader::deletePublicIfLocal($item->image);
            $item->delete();
            $this->dispatch('swal:success', ['message' => 'Item menu telah dihapus.']);
        }
    }
    
    public function resetItemForm()
    {
        $this->reset(['itemId', 'itemName', 'itemDesc', 'itemPrice', 'itemImage', 'itemImageExisting']);
        $this->itemActive = true;
        $this->itemPopular = false;
        $this->resetErrorBag();
    }
    
    public function closeItemModal()
    {
        // Hide via JS and reset input
        $this->dispatch('modal:hide', id: 'itemModal');
        $this->resetItemForm();
        $this->itemImage = null;
    }

    public function toggleItemStatus(MenuItem $item, $property)
    {
        Log::debug('MenuManagement toggleItemStatus', ['item_id' => $item->id, 'property' => $property]);
        if (in_array($property, ['is_active', 'is_popular'])) {
            $item->update([$property => !$item->$property]);
        }
    }

    public function sortItems(string $field)
    {
        Log::debug('MenuManagement sortItems', ['field' => $field]);
        if ($this->itemSort === $field) {
            $this->itemDir = $this->itemDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->itemSort = $field;
            $this->itemDir = 'asc';
        }
        $this->resetPage();
    }

    public function render()
    {
        Log::debug('MenuManagement render', [
            'selectedCategoryId' => $this->selectedCategoryId,
            'itemSearch' => $this->itemSearch,
            'itemSort' => $this->itemSort,
            'itemDir' => $this->itemDir,
        ]);
        $items = MenuItem::query()
            ->where('menu_category_id', $this->selectedCategoryId)
            ->when($this->itemSearch, fn($q) => $q->where('name', 'like', '%' . $this->itemSearch . '%'))
            ->orderBy($this->itemSort, $this->itemDir)
            ->paginate($this->perPage);

        return view('livewire.fnb.menu-management', [
            'items' => $items,
            'selectedCategory' => $this->selectedCategoryId ? $this->categories->firstWhere('id', $this->selectedCategoryId) : null,
        ]);
    }

    protected $validationAttributes = [
        'catName' => 'Nama kategori',
        'catImage' => 'Gambar kategori',
        'itemName' => 'Nama item',
        'itemPrice' => 'Harga',
        'itemImage' => 'Gambar item',
    ];
}
