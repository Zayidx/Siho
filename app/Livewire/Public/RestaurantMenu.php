<?php

namespace App\Livewire\Public;

use App\Actions\Fnb\CreateFnbOrder;
use App\Models\FnbOrder;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.public')]
#[Title('Menu Restoran')]
class RestaurantMenu extends Component
{
    use WithFileUploads;
    public $categories = [];

    public $selectedCategory = null;

    public $search = '';

    public $items = [];

    public $cart = [];

    public $note = '';

    public $roomNumber = '';

    public $serviceType = 'in_room'; // in_room, dine_in, takeaway

    public bool $showCartModal = false;

    public $proofFile; // uploaded payment proof

    public function mount(): void
    {
        // Handle quick add from query string
        $id = (int) request()->query('add', 0);
        if ($id > 0 && Auth::check()) {
            try {
                $this->addToCart($id);
            } catch (\Throwable $e) {
            }
            // Redirect to clean URL to avoid duplicate additions on refresh
            $this->redirect(route('menu'), navigate: true);

            return;
        }

        // Merge session fnb_cart to component cart (from quick-add on homepage)
        $sessionCart = session()->pull('fnb_cart', []);
        if (is_array($sessionCart) && ! empty($sessionCart)) {
            $ids = array_keys($sessionCart);
            $items = MenuItem::whereIn('id', $ids)->get();
            foreach ($items as $it) {
                if (! isset($this->cart[$it->id])) {
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
                'qty' => collect($this->cart)->sum(fn ($c) => $c['qty']),
                'items' => count($this->cart),
            ]]);
        }

        // Load categories (cache for faster first paint)
        $this->categories = Cache::tagsIfSupported(['menu'])->remember('menu:categories:active', 600, function () {
            return MenuCategory::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray();
        });
        $this->loadItems();
    }

    public function updatedSelectedCategory(): void
    {
        $this->loadItems();
    }

    private function loadItems(): void
    {
        $catId = $this->selectedCategory;
        $term = trim((string) $this->search);
        $useCache = $term === '';
        $cacheKey = 'menu:items:'.($catId ?: 'all');

        if ($useCache) {
            $data = Cache::tagsIfSupported(['menu'])->remember($cacheKey, 300, function () use ($catId) {
                $q = MenuItem::with('category:id,name')->where('is_active', true);
                if ($catId) {
                    $q->where('menu_category_id', $catId);
                }

                return $q->orderBy('name')->get()->map(function ($m) {
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
            });
            $this->items = $data;
        } else {
            $q = MenuItem::with('category:id,name')->where('is_active', true);
            if ($catId) {
                $q->where('menu_category_id', $catId);
            }
            $q->where(function ($qq) use ($term) {
                $like = '%'.$term.'%';
                $qq->where('name', 'like', $like)->orWhere('description', 'like', $like);
            });
            $this->items = $q->orderBy('name')->get()->map(function ($m) {
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
    }

    public function addToCart(int $itemId): void
    {
        $item = MenuItem::findOrFail($itemId);
        if (! isset($this->cart[$itemId])) {
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
            'qty' => collect($this->cart)->sum(fn ($c) => $c['qty']),
            'items' => count($this->cart),
        ]]);
    }

    public function inc(int $itemId): void
    {
        if (isset($this->cart[$itemId])) {
            $this->cart[$itemId]['qty'] += 1;
        }
        $this->dispatch('fnb:cart:update', [[
            'qty' => collect($this->cart)->sum(fn ($c) => $c['qty']),
            'items' => count($this->cart),
        ]]);
    }

    public function dec(int $itemId): void
    {
        if (isset($this->cart[$itemId])) {
            $this->cart[$itemId]['qty'] -= 1;
            if ($this->cart[$itemId]['qty'] <= 0) {
                unset($this->cart[$itemId]);
            }
        }
        $this->dispatch('fnb:cart:update', [[
            'qty' => collect($this->cart)->sum(fn ($c) => $c['qty']),
            'items' => count($this->cart),
        ]]);
    }

    public function remove(int $itemId): void
    {
        unset($this->cart[$itemId]);
        $this->dispatch('fnb:cart:update', [[
            'qty' => collect($this->cart)->sum(fn ($c) => $c['qty']),
            'items' => count($this->cart),
        ]]);
    }

    public function openCart(): void
    {
        $this->showCartModal = true;
    }

    public function closeCart(): void
    {
        $this->showCartModal = false;
    }

    public function getTotalProperty(): int
    {
        return collect($this->cart)->sum(function ($c) {
            return $c['qty'] * $c['price'];
        });
    }

    public function getCartQtyProperty(): int
    {
        return collect($this->cart)->sum(fn ($c) => (int) ($c['qty'] ?? 0));
    }

    public function checkout()
    {
        if (! Auth::check()) {
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
            'proofFile' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ], [
            'serviceType.required' => 'Pilih tipe layanan.',
            'serviceType.in' => 'Tipe layanan tidak valid.',
            'roomNumber.required' => 'Nomor kamar wajib diisi untuk layanan di kamar.',
            'roomNumber.max' => 'Nomor kamar maksimal 10 karakter.',
            'note.max' => 'Catatan maksimal 200 karakter.',
            'proofFile.required' => 'Silakan unggah bukti pembayaran.',
            'proofFile.mimes' => 'Format bukti harus jpg, jpeg, png, atau pdf.',
            'proofFile.max' => 'Ukuran maksimal 4MB.',
        ]);

        $order = app(CreateFnbOrder::class)->handle(Auth::id(), $this->cart, [
            'service_type' => $this->serviceType,
            'notes' => $this->note,
            'room_number' => $this->roomNumber ?: null,
        ]);

        // Store payment proof and mark for review
        try {
            $path = $this->proofFile->store('payment_proofs', 'public');
            $order->update([
                'payment_method' => 'Bank Transfer',
                'payment_proof_path' => $path,
                'payment_review_status' => 'pending',
                'payment_proof_uploaded_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
            $this->addError('proofFile', 'Gagal mengunggah bukti pembayaran. Coba lagi.');

            return; // Stop redirect to let user retry
        }

        $this->cart = [];
        $this->note = '';
        $this->roomNumber = '';
        $this->proofFile = null;
        $this->dispatch('swal:success', ['message' => 'Pesanan berhasil dibuat. Silakan tunggu konfirmasi.']);
        $this->dispatch('fnb:cart:reset');

        return $this->redirect(route('menu'), navigate: true);
    }

    public function render()
    {
        return view('livewire.public.restaurant-menu');
    }

    protected $validationAttributes = [
        'serviceType' => 'Tipe layanan',
        'roomNumber' => 'Nomor kamar',
        'note' => 'Catatan',
        'proofFile' => 'Bukti pembayaran',
    ];
}
