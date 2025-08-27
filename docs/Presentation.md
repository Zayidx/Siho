# SIHO – Script & Checklist Presentasi (Step‑by‑Step)

Tujuan: Membawakan demo end‑to‑end yang mulus (≤ 15 menit) untuk menampilkan fitur utama SIHO: katalog kamar, Booking Wizard + voucher, pembayaran (upload bukti di Step 4), tagihan, verifikasi admin, kalender ketersediaan, dan manajemen foto per tipe kamar.

---

## 0) Persiapan (H‑1 / H‑0)

1) Env & dependensi
- `cp .env.example .env` → set `APP_URL=http://127.0.0.1:8000`, DB MySQL, `MAIL_MAILER=log` (demo lokal), `QUEUE_CONNECTION=sync` (atau jalankan worker)
- `composer install` && `npm install`
- `php artisan key:generate`
- `php artisan storage:link`

2) Database demo
- (Opsional reset) `php artisan migrate:fresh --seed`
- Verifikasi akun admin/user dari seeder (lihat `database/seeders`). Jika ragu, buat akun via Register dan ubah peran di DB.

3) Menjalankan
- Dev lengkap: `composer run dev` (app + vite + queue/logs)
- Atau minimal: `php artisan serve` (App) + `php artisan queue:work` (jika MAIL async)

4) Data pendukung
- (Opsional) Buat Promo di Admin → Promos (code: `HEMAT10`, discount_rate: `0.10`, aktif; opsional kunci ke tipe kamar)
- (Opsional) Tambahkan foto untuk beberapa Tipe Kamar di Admin → Room Types → tombol Foto

Tip teknis cepat:
- “Storage 404”: `php artisan storage:link` dan akses via vhost ke folder `public` / `php artisan serve`
- Email: gunakan `MAIL_MAILER=log` (lihat `storage/logs/laravel.log`)
- MySQL error 2002: jalankan MySQL (XAMPP), cek kredensial `.env`, `php artisan config:clear`

---

## 1) Alur Demo (10–15 menit)

1) Publik – Katalog & Detail
- Buka `/rooms` → tunjukkan filter (tipe/fasilitas/harga), status tanggal penuh
- Buka satu kamar: `/rooms/{id}` → foto berdasarkan Tipe Kamar, fasilitas, kalender kecil booked

2) Booking Wizard
- Step 1 (Tanggal): pilih rentang valid
- Step 2 (Pilih kamar per tipe): tambah/kurang jumlah per tipe (tanpa daftar kamar individual)
- Step 3 (Ringkasan & Voucher): masukkan kode (mis. `HEMAT10`) → klik “Gunakan Kode” → lihat diskon & total berubah
- Step 4 (Konfirmasi & Upload Bukti): konfirmasi → sistem buat Reservasi + Tagihan → langsung tampil form upload bukti → unggah file → sukses

3) User – Tagihan & Invoice
- Buka `/user/bills`: tunjukkan tab Paid/Pending/Rejected/Unpaid (Unpaid = belum proses pembayaran)
- Buka salah satu tagihan → pratinjau bukti di modal fullscreen → unduh invoice PDF

4) Admin – Verifikasi Pembayaran
- Buka `/admin/payments`: filter Pending → Approve satu tagihan → status menjadi Paid (email log terlihat di `storage/logs/laravel.log` bila MAIL log)

5) Admin – Kalender Ketersediaan
- Buka `/admin/availability-calendar`: tunjukkan warna per status, filter Status/Tipe/No.Kamar, klik event → modal detail

6) Admin – Foto Tipe Kamar
- Buka `/admin/room-type-management` → tombol Foto pada salah satu tipe → unggah/hapus foto (berlaku untuk semua kamar tipe tsb)

Catatan narasi:
- Jelaskan bahwa ketersediaan dihitung berdasarkan overlap tanggal reservasi (bukan status global kamar)
- Tekankan keamanan pratinjau bukti: stream via route terproteksi `/user/bills/{bill}/proof`
- Voucher hanya aktif setelah tombol “Gunakan Kode”, final check & lock dilakukan di server saat konfirmasi

---

## 2) Talking Points (Slide Ringkas)

- Arsitektur: Laravel + Livewire (SPA‑like), Eloquent ORM, DomPDF, Queue Mail
- Modul: Kamar/Tipe/Fasilitas, Booking Wizard, Tagihan & Invoice, Promo/Voucher, Payments Review, Reporting, Contacts
- Foto terpusat di Tipe Kamar → semua kamar tipe tsb berbagi gambar → hemat storage & rapi
- Keamanan: middleware role, streaming bukti (bukan public URL), validasi upload, anti duplikasi voucher (lock in transaction)
- Operasional: CSV export, filter kalender, notifikasi email (log di lokal)

---

## 3) Q&A – Siap Jawab

- Q: “Bagaimana jika dua orang pakai voucher yang sama?”
  - A: Perhitungan akhir + increment `used_count` dilakukan di transaksi terkunci, mencegah melebihi `usage_limit`.
- Q: “Bisakah integrasi payment gateway?”
  - A: Ya, Step 4 dapat ditambah metode online; update bill di callback gateway.
- Q: “Kenapa bukti pakai modal iframe, bukan tab baru?”
  - A: UX cepat & aman; file disajikan via route yang memeriksa otorisasi user.
- Q: “Apakah status kamar harus diubah ke Occupied?”
  - A: Ketersediaan berbasis overlap tanggal; status global kamar tidak dikunci agar akurat lintas hari.

---

## 4) Rencana Fallback (Plan B)

- Email tidak terkirim → set `MAIL_MAILER=log`, tunjukkan log link/invoice di `storage/logs/laravel.log`
- Storage tidak menayangkan bukti → jalankan `php artisan storage:link` dan akses via vhost `public/`
- DB bermasalah → cek XAMPP/layanan MySQL, `php artisan config:clear`
- Voucher tidak aktif → periksa aktif/masa berlaku/`usage_limit`/`apply_room_type_id`

---

## 5) Penutup & Next Steps

- Integrasi payment gateway (midtrans/xendit) + webhook → update `paid_at`
- CI/CD (lint Pint, build Vite), health checks
- Audit security (rate limit upload, CSP headers)
- Observability (log terstruktur, metrics revenue/occupancy)

