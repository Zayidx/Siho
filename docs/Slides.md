---
marp: true
paginate: true
theme: default
class: lead
---

# SIHO — Sistem Informasi Hotel
Laravel + Livewire

Presentasi (ID)

Pembicara: (isi nama Anda)
Tanggal: (isi tanggal)

---

## Agenda

- Gambaran & Arsitektur
- Fitur Utama & Demo Alur
- Voucher & Pembayaran (Step 4)
- Modul Admin (Kalender, Foto Tipe)
- Keamanan & Deployment
- Q&A

---

## Arsitektur Singkat

- Backend: Laravel 12 + Eloquent ORM
- UI: Blade + Livewire (SPA‑like)
- PDF: DomPDF (invoice)
- Email: Queue/Sync (dev)
- File: disk `public` (foto tipe, bukti bayar)

---

## ERD (Ringkas)

```mermaid
erDiagram
  USERS ||--o{ RESERVATIONS : has
  ROOMS ||--o{ RESERVATIONS : assigned
  ROOM_TYPES ||--o{ ROOMS : has
  ROOM_TYPES ||--o{ ROOM_IMAGES : images
  ROOM_TYPES }o--o{ FACILITIES : features
  RESERVATIONS ||--o{ BILLS : has
  BILLS ||--o{ PAYMENT_LOGS : logs
  PROMOS }o--o{ ROOM_TYPES : applies?
```

---

## Fitur Utama

- Katalog & Detail kamar (foto per Tipe)
- Booking Wizard (4 langkah)
- Voucher (apply via tombol)
- Konfirmasi + Upload Bukti (Step 4)
- Tagihan (invoice PDF, preview bukti)
- Admin: Payments Review, Kalender, Foto Tipe, Pelaporan

---

## Demo Alur (Ringkas)

1) `/rooms` → filter & pilih kamar
2) Booking Wizard:
   - Step 1: tanggal valid
   - Step 2: jumlah per Tipe
   - Step 3: voucher → “Gunakan Kode”
   - Step 4: konfirmasi → upload bukti
3) `/user/bills` → preview bukti, unduh invoice
4) Admin → Payments Review: Approve
5) Admin → Kalender: warna status + filter

---

## Voucher (Aman & Terkendali)

- Tombol “Gunakan Kode” → diskon aktif
- Validasi: aktif, periode, limit, opsional per Tipe
- Final check saat konfirmasi (server‑side, lock promo)
- `used_count` di‑increment di transaksi (hindari race)

---

## Pembayaran (Step 4)

- Konfirmasi → buat Reservasi + Bill
- Tampilkan form Upload Bukti (tanpa Step 5)
- Notifikasi admin; status Pending
- Preview bukti via route terproteksi

---

## Admin: Kalender Ketersediaan

- Warna per status (Confirmed/Checked‑in/Completed/Cancelled)
- Filter: Status, Tipe, Nomor Kamar
- Klik event → modal detail

---

## Admin: Foto Tipe Kamar

- Kelola di `/admin/room-types/{type}/images`
- Berlaku untuk semua kamar pada Tipe tsb
- Repo lebih bersih, tanpa duplikasi foto

---

## Keamanan & Praktik Baik

- Middleware role untuk rute user/admin
- Streaming bukti (bukan URL publik)
- Validasi upload (tipe/ukuran)
- Queue mail di produksi

---

## Deployment Cepat

1) `composer install --no-dev` + `npm ci && npm run build`
2) `.env` produksi + `APP_KEY`
3) `php artisan migrate --force && storage:link`
4) Cache: `config/route/view`
5) Jalankan queue worker

---

## Q&A (Contoh)

- Concurrency voucher? → transaksi terkunci, `used_count` aman
- Payment gateway? → Step 4 mudah diintegrasi, update bill via webhook
- Kenapa modal bukti? → UX cepat & route terproteksi
- Ketersediaan kamar? → berbasis overlap tanggal, akurat lintas hari

---

## Terima Kasih

Repo & Dokumentasi: README.md / docs/

(Kontak / QR / Email Anda)

