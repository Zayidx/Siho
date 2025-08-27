# Dokumentasi Sistem Hotel (SIHO)

Dokumen ini menjelaskan gambaran sistem, modul, arsitektur, cara setup, serta alur pengguna untuk aplikasi Sistem Hotel (SIHO) berbasis Laravel + Livewire.

## Ringkasan Sistem
- Fokus: manajemen kamar, tipe kamar & fasilitas, reservasi, tagihan/pembayaran, promosi, pelaporan, dan komunikasi (contact form).
- Peran utama: Pengunjung Publik (tanpa login), Tamu/Pengguna (role: `user`/`users`), dan Admin (role: `superadmin`).
- Teknologi: Laravel, Livewire, Blade, Vite, Eloquent ORM, DomPDF, Queue/Mail.

## Fitur Utama
- Katalog kamar publik: pencarian, filter harga/tipe/fasilitas, rekomendasi, tanggal penuh.
- Detail kamar: foto, tipe & fasilitas, rentang tanggal ter-booking, rekomendasi kamar serupa.
- Booking Wizard: pilih tanggal/kamar, hitung subtotal, pajak, service fee, dan diskon promo dari DB.
- Reservasi & Tagihan: pembuatan otomatis reservasi + tagihan; opsi pembayaran manual atau online (mock) + log pembayaran.
- Manajemen admin: kamar, tipe kamar, fasilitas, tamu/pengguna, reservasi (dengan alokasi kamar by type), housekeeping (placeholder), foto kamar, promo, verifikasi pembayaran, pelaporan, pesan kontak.
- Ekspor CSV: users, rooms, reservations, bills, contacts.
- Verifikasi email & ganti email dengan tautan bertanda tangan (signed URL).

## Peran & Hak Akses
- Publik: melihat daftar/detail kamar, memulai booking; redirect ke login saat konfirmasi.
- Pengguna (user/users): dashboard, profil, daftar reservasi & detail, tagihan + unduh invoice PDF.
- Admin (superadmin): seluruh modul admin (dashboard, user/guest, rooms/room types/facilities, reservations, housekeeping, reporting, payments review, promos, contacts, image management, ekspor CSV).
- Pembatasan akses: middleware `CheckRole` pada prefix `admin` (role: `superadmin`) dan `user` (role: `user,users`).

## Arsitektur & Teknologi
- Backend: Laravel, Eloquent ORM.
- Frontend: Blade + Livewire components (SPA-like interactivity), Vite untuk assets.
- PDF: `barryvdh/laravel-dompdf` untuk invoice.
- Email: Queue Mail untuk verifikasi email dan notifikasi.
- Penyimpanan file: disk `public` untuk foto kamar dan bukti pembayaran.

## Struktur Proyek
- `app/Livewire`: komponen UI (Admin, Auth, Public, User, BookingWizard).
- `app/Models`: model inti (User, Role, Rooms, RoomType, Reservations, Bills, Facility, Promo, ContactMessage, RoomImage, PaymentLog).
- `routes/web.php`: rute publik, user, admin; termasuk ekspor CSV dan unduh invoice.
- `database/migrations`: skema tabel, pivot, dan kolom tambahan.
- `resources/views`: blade views untuk Livewire dan layouts.
- `app/Http/Controllers`: kontroler spesifik (invoice PDF, verifikasi email, ekspor CSV admin).

## Setup Lokal
1) Persiapan
- `cp .env.example .env` lalu set `APP_KEY` (`php artisan key:generate`), koneksi DB, mail.
- Install: `composer install` dan `npm install`.

2) Migrasi & seeding
- Untuk pertama kali atau setelah perubahan struktur migrasi ini, disarankan: `php artisan migrate:fresh` (tambahkan `--seed` bila perlu).
- Jika database sudah terisi dan Anda tidak ingin menghapus data, evaluasi perubahan migrasi terlebih dulu sebelum menjalankan.

3) Jalankan
- App + queue + logs + Vite: `composer run dev`.
- Server PHP saja: `php artisan serve`.
- Frontend dev: `npm run dev`; produksi: `npm run build`.
- Storage publik: `php artisan storage:link` untuk mengaktifkan akses file upload (foto kamar, bukti bayar).

4) Testing & format
- Test: `composer test` atau `php artisan test`.
- Format PHP (lokal): `./vendor/bin/pint`.

Catatan: Jangan commit `.env`/secret. Pastikan disk `public` ter-setup untuk upload foto kamar/bukti bayar.

## Modul & Alur Bisnis

### 1) Katalog & Booking Publik
- Daftar kamar (`App\Livewire\Public\RoomsList`):
  - Filter: pencarian teks, tipe kamar, fasilitas (via relasi tipe kamar), kapasitas minimal, rentang harga, sort.
  - Tanggal penuh: kalkulasi 45 hari ke depan; hari tanpa ketersediaan diberi disabled.
- Detail kamar (`App\Livewire\Public\RoomDetail`):
  - Menampilkan foto, tipe+fasilitas, dan 8 rentang tanggal booking mendatang; juga kamar serupa (tipe sama, harga terurut).
- Booking Wizard (`App\Livewire\BookingWizard`):
  - Step 1: pilih tanggal; validasi tgl.
  - Step 2: pilih jumlah kamar per tipe (tambah/kurang) sesuai ketersediaan pada rentang tanggal (tanpa menampilkan seluruh kamar).
  - Step 3: ringkasan + voucher (Promo aktif/valid, limit penggunaan, filter tipe kamar bila diset), pajak (10%) dan service fee flat; total dihitung real-time.
  - Step 4: konfirmasi; butuh login. Saat konfirmasi, sistem buat Reservasi, attach kamar, update status kamar Occupied, buat Tagihan.
  - Step 5: pembayaran: Manual (status review pending + notifikasi admin) atau Online (mock langsung paid/approved) plus `PaymentLog`.

### 2) Reservasi & Tagihan (Pengguna)
- Halaman pengguna:
  - `User\Reservations`, `User\ReservationDetail`: daftar & detail reservasi milik user.
  - `User\Bills`: daftar tagihan; unduh invoice via `User\InvoiceController` (PDF DomPDF) pada rute `user.bills.invoice`.
  - `User\Profile`: profil pengguna; dukung verifikasi email & ganti email (pending_email + signed URL).

### 3) Verifikasi & Pembayaran (Admin)
- Payments Review (`Admin\PaymentsReview`): filter status (pending/approved/rejected), pencarian; hanya menampilkan yang belum `paid_at` (kecuali online mock yang langsung paid).
- Export Bills (`Admin\BillsExportController@csv`): filter status, pencarian, rentang tanggal upload bukti.
- Log Pembayaran (`payment_logs`): mencatat aksi user/admin dan metode.

### 4) Manajemen Kamar, Tipe, Fasilitas, Foto
- Rooms (`Admin\RoomManagement`): CRUD kamar, status (Available/Occupied), harga per malam, lantai, deskripsi.
- Room Types (`Admin\RoomTypeManagement`): nama, deskripsi, base_price, kapasitas, relasi banyak ke `facilities` via pivot `facility_room_type`.
- Facilities (`Admin\FacilityManagement`): nama, ikon (opsional); relasi ke tipe kamar.
- Room Images (`Admin\RoomImages`): upload multi-foto ke disk `public`, urutan `sort_order`, hapus; ditampilkan di detail kamar.

### 5) Reservasi (Admin)
- Reservation Management (`Admin\ReservationManagement`):
  - Buat/Edit: pilih tamu (atau buat tamu baru cepat), tanggal, status, catatan, dan jumlah kamar per tipe; sistem memilih kamar available dan sinkron status Occupied/Available otomatis.
  - Filter: pencarian (nama/email tamu, status), status, tanggal check-in/out; pagination.
  - Cegah hapus jika status Checked-in; detach kamar + kembalikan Available saat delete.

### 6) Promo
- Promo Management (`Admin\PromoManagement`): code uppercase unik, nama, discount_rate 0..1, aktif, periode, room_type terapan (opsional), usage_limit.
- Booking Wizard: validasi promo aktif/valid, limit terpakai, dan kecocokan tipe kamar sebelum beri diskon; increment `used_count` saat konfirmasi.

### 7) Pelaporan
- Reporting (`Admin\Reporting`):
  - Revenue per hari: akumulasi `bills.total_amount` pada rentang tanggal.
  - Occupancy: persentase okupansi berdasar count reservasi aktif vs total kamar per hari (periode dipilih).

### 8) Pesan Kontak
- Public Contact Form (`Livewire\Public\ContactForm`): validasi, honeypot anti-bot, rate limit sederhana per IP, simpan ke `contact_messages`, kirim email ke admin dan auto-reply ke pengirim.
- Admin Contacts (`Admin\ContactMessages`): daftar & ekspor CSV via `ContactExportController`.

## Skema Basis Data (inti)
- `users` ↔ `roles` (many-to-one) dengan field profil tamu (full_name, phone, address, id_number, foto, date_of_birth) dan `pending_email`.
- `rooms` ↔ `room_types` (many-to-one) + `room_images` (one-to-many).
- `room_types` ↔ `facilities` (many-to-many via `facility_room_type`).
- `reservations` ↔ `users` (many-to-one sebagai `guest_id`).
- `reservations` ↔ `rooms` (many-to-many via `reservation_room`, plus `assigned_at`).
- `bills` ↔ `reservations` (many-to-one), dengan kolom bukti & status review pembayaran.
- `bills` menyimpan rincian biaya: `subtotal_amount`, `discount_amount`, `tax_amount`, `service_fee_amount`, serta `total_amount`.
- `payment_logs` mencatat aksi terkait bills.
- `promos` (kode unik, rate, periode, usage counters) opsional terikat ke `room_types`.
- `contact_messages` (nama, email, pesan, ip, read_at).

## Rute Penting (ringkas)
- Publik: `/`, `/login`, `/register`, `/rooms`, `/rooms/{room}`, `/booking`, `/booking/{room}/confirm`, `/booking-wizard`.
- Verifikasi Email: `/email/verify-new`, `/email/verify-current`, `/email/resend`.
- User (auth + role user/users): `/user/dashboard`, `/user/reservations`, `/user/reservations/{id}`, `/user/bills`, `/user/bills/{bill}/invoice`, `/user/profile`.
- Admin (auth + role superadmin): `/admin/...` untuk dashboard, manajemen data, pelaporan, payments, promos, contacts, images, dan ekspor CSV.

## Ekspor Data
- Users, Rooms, Reservations, Bills, Contacts: endpoint `.../export` menghasilkan CSV ter-stream untuk skala data besar (chunked).

## Testing & Kualitas
- Jalankan `composer test` untuk PHPUnit. Tempatkan test di `tests/Feature` dan `tests/Unit`.
- Ikuti PSR-12; gunakan `./vendor/bin/pint` untuk formatting lokal.

## Keamanan & Konfigurasi
- Jangan commit `.env` dan secret. Set `APP_KEY`, DB, dan Mail dengan benar.
- Gunakan queue untuk mail agar respons UI tetap cepat.
- Pembatasan akses via middleware role. Validasi input Livewire & controller telah diterapkan.
- Unggahan file (bukti/foto) ke disk `public`; pastikan hak akses filesystem aman.

## FAQ Singkat
- Bagaimana menerapkan promo per tipe kamar? Set `apply_room_type_id` di Promo, maka diskon hanya aktif bila setidaknya satu kamar tipe tersebut dipilih.
- Bagaimana status pembayaran manual? User memilih Manual → status `payment_review_status = pending`; admin meninjau di modul Payments Review.
- Apakah pembayaran online terintegrasi gateway? Saat ini mock: `paid_at` langsung diisi dan status `approved`.
