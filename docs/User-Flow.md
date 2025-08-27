# User Flow Sistem Hotel (SIHO)

Dokumen ini merinci alur pengguna untuk masing-masing peran.

## 1) Pengunjung Publik
- Buka `GET /rooms`:
  - Lihat daftar kamar dengan filter: kata kunci, tipe kamar, fasilitas, kapasitas, rentang harga, urutan.
  - Tanggal penuh 45 hari ke depan ditandai tidak tersedia.
- Buka `GET /rooms/{room}`:
  - Lihat detail kamar, foto, fasilitas, periode booking yang sudah terisi, dan kamar serupa.
- Mulai booking:
  - `GET /booking-wizard` → ikuti langkah 1–4 (tanggal, pilih kamar per tipe, ringkasan, konfirmasi).
- Saat konfirmasi, jika belum login → redirect ke `GET /login`.

## 2) Pengguna (Tamu) — setelah login
- Dashboard `GET /user/dashboard`: ringkasan reservasi/tagihan terbaru.
- Reservasi:
  - `GET /user/reservations`: daftar reservasi milik user, pencarian/paginasi.
  - `GET /user/reservations/{id}`: detail reservasi (kamar, tanggal, status, tagihan).
- Tagihan:
  - `GET /user/bills`: daftar tagihan; aksi unduh invoice PDF melalui `GET /user/bills/{bill}/invoice`.
- Profil `GET /user/profile`: ubah profil; verifikasi email dan ganti email via tautan bertanda tangan (signed URL).

### Alur Booking Wizard detail (User)
- Step 1 (Tanggal): validasi `checkin >= today`, `checkout > checkin`.
- Step 2 (Pilih kamar per tipe): pilih jumlah per tipe via tombol tambah/kurang, dibatasi ketersediaan (tanpa menampilkan seluruh kamar).
- Step 3 (Ringkasan): subtotal = sum(harga x malam), pajak 10%, service fee flat, voucher (kode promo) → diskon bila promo aktif/valid, limit belum habis, dan (jika di-set) cocok tipe kamar.
- Step 4 (Konfirmasi): buat record `reservations`, attach kamar, set kamar `Occupied`, buat `bills`.
- Step 5 (Pembayaran):
  - Manual: set `payment_method=Manual`, `payment_review_status=pending`, buat `payment_logs` aksi `manual_submit`, kirim email notifikasi admin. User diarahkan ke daftar reservasi.
  - Online (mock): set `payment_method=Online`, `paid_at=now()`, `payment_review_status=approved`, buat `payment_logs` aksi `online_paid`.

## 3) Admin (superadmin)
- Dashboard `GET /admin/dashboard`.
- Users & Guests:
  - `GET /admin/usermanagement`, `GET /admin/guestmanagement`.
  - Ekspor users: `GET /admin/users/export` (CSV).
- Rooms & Room Types & Facilities:
  - `GET /admin/roommanagement`, `GET /admin/room-type-management`, `GET /admin/facility-management`.
  - Kelola foto kamar: `GET /admin/rooms/{room}/images` (upload/hapus).
  - Ekspor rooms: `GET /admin/rooms/export` (CSV).
- Reservations:
  - `GET /admin/reservationmanagement`: buat/edit; pilih user tamu atau buat tamu cepat; tentukan jumlah kamar per tipe; sistem memilih kamar available dan sinkron status.
  - Ekspor: `GET /admin/reservations/export` (CSV).
- Availability & Housekeeping:
  - `GET /admin/availability-calendar` (events via `GET /admin/calendar-events`).
  - `GET /admin/housekeeping` untuk manajemen housekeeping (placeholder jika UI minimal).
- Payments Review `GET /admin/payments`:
  - Filter by `payment_review_status`, pencarian, range tanggal bukti.
  - Ekspor bills CSV: `GET /admin/payments/export`.
- Reporting `GET /admin/reporting`:
  - Revenue harian dari `bills.total_amount` pada periode.
  - Occupancy harian (reservasi aktif/total kamar).
- Contacts `GET /admin/contacts`:
  - Daftar pesan kontak; ekspor: `GET /admin/contacts/export`.
- Promos `GET /admin/promos`:
  - CRUD promo (code uppercase unik, rate 0..1, aktif, periode, limit, opsional tipe kamar).

## 4) Verifikasi Email & Ganti Email
- Verifikasi email saat ini: `GET /email/verify-current?email=...` (signed URL). Set `email_verified_at` jika cocok.
- Verifikasi email baru (ganti email): `GET /email/verify-new?user=...&email=...` (signed URL). Pindahkan `pending_email` → `email`.
- Resend link: `GET /email/resend` (auth).

## 5) Contact Form (Publik)
- Submit melalui Livewire:
  - Validasi, honeypot (field `website` harus kosong), rate limit 5/10 menit per IP.
  - Simpan ke `contact_messages`, kirim email ke admin (`config('mail.contact_to')`/`mail.from`) dan auto-reply ke pengirim.

## 6) Status & Kondisi Penting
- Status kamar: `Available` ↔ `Occupied` otomatis saat attach/detach pada reservasi.
- Status reservasi: `Confirmed`, `Checked-in`, `Completed`, `Cancelled` (mempengaruhi logika penghapusan & ketersediaan kamar).
- Pembayaran:
  - Manual: `payment_review_status = pending|approved|rejected`; `paid_at` terisi saat approved.
  - Online (mock): langsung `paid_at` + `approved`.
- Promo: hanya aktif bila berada pada rentang waktu valid, jumlah penggunaan belum mencapai limit, dan (opsional) cocok tipe kamar terpilih.

## 7) Error & Notifikasi
- Notifikasi UI via event Livewire (`swal:*`).
- Email queue untuk notifikasi admin dan verifikasi pengguna.

## 8) Ekspor CSV
- Endpoint admin `.../export` menggunakan stream + chunk untuk performa.

---

Catatan: Implementasi pembayaran online masih mock. Integrasi gateway dapat ditambahkan pada alur Step 5 dengan callback webhook untuk set `paid_at` dan log aksi.
