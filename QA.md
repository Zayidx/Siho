# QA & Dokumentasi — Modul Restoran (F&B)

## 1) Gambaran Umum
- Tujuan: Allow guests to order food and beverages from a public menu, show popular items on the homepage, and enable F&B cashier staff to manage orders and menu inventory.
- Fitur utama:
  - Halaman pemesanan publik (`/menu`) dengan kategori, pencarian, keranjang, tipe layanan (di kamar, makan di tempat, bawa pulang), catatan, nomor kamar (opsional), dan checkout.
  - Seksi Menu Populer di beranda (dari item bertanda `is_popular`).
  - Dashboard Kasir: melihat/memproses pesanan, ubah status, tandai lunas.
  - Manajemen Menu F&B: kelola kategori & item (aktif, populer, gambar, harga).
  - Riwayat pesanan pengguna dan pembatalan saat status masih pending.

## 2) Arsitektur
- Data models:
  - `MenuCategory` (id, name, description, is_active)
  - `MenuItem` (menu_category_id, name, description, price, is_active, is_popular, image)
  - `FnbOrder` (user_id, status, payment_status, service_type, total_amount, room_number, notes)
  - `FnbOrderItem` (order_id, menu_item_id, qty, unit_price, line_total)
- Komponen Livewire:
  - Public: `App\Livewire\Public\RestaurantMenu` → `resources/views/livewire/public/restaurant-menu.blade.php`
  - Cashier: `App\Livewire\Fnb\CashierDashboard` → `resources/views/livewire/fnb/cashier-dashboard.blade.php`
  - F&B inventory: `App\Livewire\Fnb\MenuManagement` → `resources/views/livewire/fnb/menu-management.blade.php`
  - User: `App\Livewire\User\FnbOrders` → `resources/views/livewire/user/fnb-orders.blade.php`
- Controller: `HomeController@index` loads `popularMenus` for the welcome view.
- Service (Action): `App\Actions\Fnb\CreateFnbOrder` encapsulates transactional order creation.
- Policy/Otorisasi:
  - `App\Policies\FnbOrderPolicy` bound in `App\Providers\AuthServiceProvider` (registered in `bootstrap/app.php`).

## 3) Basis Data
- Migrations:
  - `2025_09_03_000100_create_menu_categories_table.php`
  - `2025_09_03_000110_create_menu_items_table.php`
  - `2025_09_03_000120_create_fnb_orders_table.php`
  - `2025_09_03_000130_create_fnb_order_items_table.php`
  - `2025_09_03_001000_alter_fnb_orders_add_service_type.php
  - `2025_09_03_010000_alter_menu_categories_add_image.php``
- Seeder:
  - `Database\Seeders\FnbSeeder` creates 3 categories and 6 items (some popular).

## 4) Rute & Peran
- Public:
  - `/menu` → restaurant menu and ordering (login required to checkout)
  - Homepage popular menu section → from `MenuItem::where(is_popular)`
- Cashier (requires role `cashier` or `superadmin`):
  - `/cashier/fnb` → orders list, status updates, mark paid
  - `/cashier/fnb/menu` → categories/items management
- User (requires role `user` or `users`):
  - `/user/fnb/orders` → my orders (with cancel if pending)
- Middleware: alias `role` (see `bootstrap/app.php`).
- Policies: `FnbOrderPolicy` restricts updates/markPaid to cashier/superadmin and cancel to the owner while pending.

## 5) Status Pesanan & Enumerasi
- Statuses (FnbOrder constants): `pending`, `preparing`, `ready`, `served`, `cancelled`.
- Payments: `unpaid`, `paid`.
- Service types: `in_room`, `dine_in`, `takeaway`.
- Helpers in `FnbOrder`:
  - `isCancelable()` → only `pending`.
  - `setStatusSafe($status)` → validates allowed statuses.
  - `markPaid()` → sets `payment_status=paid`.

## 6) Cara Kerja (Alur)
- Public ordering (`/menu`):
  1. User memilih kategori atau mencari menu; klik “Tambah” untuk memasukkan item ke keranjang.
  2. Pilih tipe layanan (Di Kamar/Makan di Restoran/Bawa Pulang), isi nomor kamar & catatan bila perlu.
  3. Klik “Pesan Sekarang”. Komponen memanggil `CreateFnbOrder` untuk membuat order + item dalam transaksi; total dihitung dari keranjang.
  4. Keranjang dikosongkan setelah sukses; user dapat melihat status di `/user/fnb/orders`.
- Cashier dashboard (`/cashier/fnb`):
  1. Pesanan baru tampil dengan status `pending` (auto-refresh).
  2. Kasir ubah status menjadi `preparing` → `ready` → `served` (atau `cancelled`).
  3. Kasir klik “Tandai Lunas” untuk set `payment_status=paid` jika sudah dibayar.
- Menu management (`/cashier/fnb/menu`):
  - Tambah/Edit/Hapus kategori dan item; toggle `is_active` & `is_popular`, unggah gambar.
- User order history (`/user/fnb/orders`):
  - Lihat daftar pesanan; batalkan pesanan bila masih `pending`.

## 7) Setup & Prasyarat
- Jalankan migrasi: `php artisan migrate`
- Seed data F&B: `php artisan db:seed --class=FnbSeeder`
- Pastikan roles:
  - Tabel `roles` memiliki `users`, `cashier`, `superadmin`. Tetapkan role yang sesuai ke pengguna.
- Storage symlink (untuk gambar menu): `php artisan storage:link`
- Frontend:
  - Alpine.js dipakai pada beberapa form; pastikan dimuat via Vite/layout.

## 8) Rencana Uji Manual
- Public Menu
  - [ ] Kategori menampilkan item yang sesuai.
  - [ ] Pencarian (nama/desk) memfilter secara live.
  - [ ] Tambah ke keranjang, ubah kuantitas, total berubah.
  - [ ] Checkout gagal saat tidak login → redirect ke login.
  - [ ] Checkout sukses saat login → order `pending/unpaid`, item sesuai, total benar.
  - [ ] Tipe layanan disimpan sesuai pilihan; nomor kamar/catatan opsional tersimpan.
- Homepage Popular Menu
  - [ ] Seksi “Menu Populer” muncul jika ada `is_popular=true`; tombol “Pesan” ke `/menu`.
- Cashier Dashboard
  - [ ] User dengan role `cashier`/`superadmin` bisa mengakses; user biasa 403.
  - [ ] Ubah status: `pending` → `preparing` → `ready` → `served`/`cancelled`.
  - [ ] “Tandai Lunas” mengubah `payment_status=paid`.
  - [ ] Auto-refresh menampilkan order baru.
- Menu Management
  - [ ] Tambah kategori dan item baru (dengan gambar), item tampil di `/menu`.
  - [ ] Toggle `is_popular` mempengaruhi tampilan di homepage.
  - [ ] Nonaktifkan item → tidak muncul di `/menu`.
- User Orders
  - [ ] Riwayat pesanan tampil; `pending` dapat dibatalkan; yang lain tidak dapat.
- Authorization (Negatif)
  - [ ] Non-kasir mencoba ubah status/mark paid → 403.
  - [ ] User mencoba batalkan pesanan orang lain → 403.

## 9) Pengujian Otomatis
- Menjalankan: `composer test`
- Cakupan saat ini:
  - Unit: `tests/Unit/FnbOrderTest.php` (konstanta, helper status, markPaid).
  - Feature: `tests/Feature/RestaurantMenuOrderTest.php` (checkout Livewire).
  - Feature: `tests/Feature/CashierDashboardActionsTest.php
  - Feature: `tests/Feature/RestaurantQuickAddMergeTest.php` (quick-add homepage → merge session ke Livewire cart).` (hak kasir vs non‑kasir).

## 10) Troubleshooting
- 403 di halaman kasir → pastikan role user adalah `cashier` atau `superadmin`.
- “Menu Populer” kosong → tandai beberapa item sebagai `is_popular=true` di `/cashier/fnb/menu`.
- Gambar tidak tampil → jalankan `php artisan storage:link`; pastikan file tersimpan di `storage/app/public/menu`.
- Checkout tidak muncul/keranjang kosong → pastikan login dan ada item aktif.
- Komponen tidak responsif → pastikan Alpine.js dimuat.

## 11) Pengembangan Lanjutan
- Notifikasi real‑time (WebSockets) untuk pesanan baru ke kasir.
- Auto‑popular berdasarkan penjualan (top N item).
- Cetak tiket dapur / invoice.
- Diskon/promo per item atau per order.
- Pembayaran online dan integrasi payment gateway.
