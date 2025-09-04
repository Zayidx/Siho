<?php

namespace App\Livewire\Public;

use App\Actions\Fnb\CreateFnbOrder;
use App\Models\FnbOrder;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.public')]
#[Title('Menu Restoran')]
class RestaurantMenu extends Component
{
    public $categories = [];
    public $selectedCategory = null;
    public $search = '';
    public $items = [];
    public $cart = [];
    public $note = '';
    public $roomNumber = '';
    public $serviceType = 'in_room'; // in_room, dine_in, takeaway

    public function mount(): void
    {
        // Handle quick add from query string
        $id = (int) request()->query('add', 0);
        if ($id > 0 && auth()->check()) {
            try { $this->addToCart($id); } catch (\Throwable $e) {}
            // Redirect to clean URL to avoid duplicate additions on refresh
            $this->redirect(route('menu'), navigate: true);
            return;
        }

        // Merge session fnb_cart to component cart (from quick-add on homepage)
        $sessionCart = session()->pull('fnb_cart', []);
        if (is_array($sessionCart) && !empty($sessionCart)) {
            $ids = array_keys($sessionCart);
            $items = MenuItem::whereIn('id', $ids)->get();
            foreach ($items as $it) {
                if (!isset($this->cart[$it->id])) {
                    $this->cart[$it->id] = [
                        'id' => $it->id,
                        'name' => $it->name,
                        'price' => $it->price,
                        'qty' => 0,
                    ];
                }
                $this->cart[$it->id]['qty'] += (int) ($sessionCart[$it->id] ?? 0);
            }
            $this->dispatch('fnb:cart:update', [[
                'qty' => collect($this->cart)->sum(fn($c)=>$c['qty']),
                'items' => count($this->cart),
            ]]);
        }

        // Load categories and items
        $this->categories = MenuCategory::where('is_active', true)
            ->orderBy('name')
            ->get(['id','name'])
            ->toArray();
        $this->loadItems();
    }

    public function updatedSelectedCategory(): void
    {
        $this->loadItems();
    }

    private function loadItems(): void
    {
        $q = MenuItem::with('category:id,name')->where('is_active', true);
        if ($this->selectedCategory) {
            $q->where('menu_category_id', $this->selectedCategory);
        }
        if ($this->search) {
            $term = '%'.trim($this->search).'%';
            $q->where(function($qq) use ($term){
                $qq->where('name', 'like', $term)->orWhere('description', 'like', $term);
            });
        }
        $this->items = $q->orderBy('name')->get()->map(function($m){
            return [
                'id' => $m->id,
                'name' => $m->name,
                'description' => $m->description,
                'price' => $m->price,
                'image' => $m->image,
                'category_name' => optional($m->category)->name,
                'is_popular' => (bool) $m->is_popular,
            ];
        })->toArray();
    }

    public function addToCart(int $itemId): void
    {
        $item = MenuItem::findOrFail($itemId);
        if (!isset($this->cart[$itemId])) {
            $this->cart[$itemId] = [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'qty' => 0,
            ];
        }
        $this->cart[$itemId]['qty'] += 1;
        $this->dispatch('swal:success', ['message' => 'Ditambahkan ke keranjang']);
        $this->dispatch('fnb:cart:update', [[
            'qty' => collect($this->cart)->sum(fn($c)=>$c['qty']),
            'items' => count($this->cart),
        ]]);
    }

    public function inc(int $itemId): void
    {
        if (isset($this->cart[$itemId])) {
            $this->cart[$itemId]['qty'] += 1;
        }
        $this->dispatch('fnb:cart:update', [[
            'qty' => collect($this->cart)->sum(fn($c)=>$c['qty']),
            'items' => count($this->cart),
        ]]);
    }

    public function dec(int $itemId): void
    {
        if (isset($this->cart[$itemId])) {
            $this->cart[$itemId]['qty'] -= 1;
            if ($this->cart[$itemId]['qty'] <= 0) unset($this->cart[$itemId]);
        }
        $this->dispatch('fnb:cart:update', [[
            'qty' => collect($this->cart)->sum(fn($c)=>$c['qty']),
            'items' => count($this->cart),
        ]]);
    }

    public function remove(int $itemId): void
    {
        unset($this->cart[$itemId]);
        $this->dispatch('fnb:cart:update', [[
            'qty' => collect($this->cart)->sum(fn($c)=>$c['qty']),
            'items' => count($this->cart),
        ]]);
    }

    public function getTotalProperty(): int
    {
        return collect($this->cart)->sum(function($c){ return $c['qty'] * $c['price']; });
    }

    public function checkout()
    {
        if (!Auth::check()) {
            return $this->redirect(route('login'), navigate: true);
        }
        if (empty($this->cart)) {
            $this->addError('cart', 'Keranjang kosong.');
            return;
        }
        // Validate service type and optional room number/note
        $this->validate([
            'serviceType' => 'required|in:in_room,dine_in,takeaway',
            'roomNumber' => $this->serviceType === FnbOrder::SERVICE_IN_ROOM
                ? 'required|string|max:10'
                : 'nullable|string|max:10',
            'note' => 'nullable|string|max:200',
        ]);

        $order = app(CreateFnbOrder::class)->handle(Auth::id(), $this->cart, [
            'service_type' => $this->serviceType,
            'notes' => $this->note,
            'room_number' => $this->roomNumber ?: null,
        ]);

        $this->cart = [];
        $this->note = '';
        $this->roomNumber = '';
        $this->dispatch('swal:success', ['message' => 'Pesanan berhasil dibuat. Silakan tunggu konfirmasi.']);
        $this->dispatch('fnb:cart:reset');
        return $this->redirect(route('menu'), navigate: true);
    }

    public function render()
    {
        return view('livewire.public.restaurant-menu');
    }
}
