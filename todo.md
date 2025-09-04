# Daftar QA (Pengujian)

## Persiapan
- Jalankan migrasi dan seeding: `php artisan migrate` lalu `php artisan db:seed --class=RolesSeeder`.
- Untuk uji email aman, set `MAIL_MAILER=log` agar email tercatat di log.

## Manajemen Inventory
- Buat inventory untuk satu tipe kamar dalam rentang tanggal (mis. 3 hari, quantity=1).
- Di Booking Wizard, pastikan ketersediaan tipe tersebut tidak melebihi 1 pada tanggal itu.
- Coba pilih 2 kamar tipe tersebut lalu konfirmasi: harus ditolak karena batas inventory.
- Ubah/hapus baris inventory dan pastikan ketersediaan di Booking ikut menyesuaikan.
- Sidebar admin menampilkan badge “Inventory” berisi jumlah entri inventory mendatang dengan kuantitas 0; pastikan jumlahnya berubah saat inventory diubah.

### Inventori Barang per Kamar (Seeder)
- Setelah seeding (HotelSeeder), cek tabel `room_item_inventories`:
  - Tiap kamar memiliki item default: Kasur (sesuai kapasitas), Keset (1), Shampoo (kapasitas), Sabun (kapasitas), Handuk (kapasitas).
- Pastikan path gambar seed `assets/image.png` tersedia di disk `public` (akses via `public/storage/assets/image.png`).

## Kategori Galeri
- Di Admin → Room Type Images, set kategori foto: facade, facilities, public, restaurant, room.
- Di beranda, bagian Galeri menampilkan 5 slide dengan thumbnail berlabel (satu per kategori).
- Jika ada kategori belum diisi, sistem memakai fallback (foto terbaru) tanpa error.
- Halaman publik baru: `/gallery` menampilkan grid gambar dengan filter kategori dan pagination. Uji:
  - Filter tiap kategori menampilkan gambar sesuai kategori.
  - Pagination bekerja, tidak merusak filter.
  - Link “Lihat Semua Galeri” di beranda menuju ke halaman ini.

## Form Kontak
- Kirim form dengan Nama, Email, Subjek, Telepon, Pesan.
- Cek tabel `contact_messages`, kolom subject/phone terisi.
- Cek log email (mailer=log), email ke admin berisi subject/phone.
- Export CSV dari Admin → Pesan Kontak; pastikan kolom subject/phone ada dan nilai tersanitasi (tidak mengeksekusi formula di Excel).

## Booking Wizard
- Step 3 (Ringkasan): centang beberapa “Permintaan Khusus” (opsi statis) dan isi “Lainnya” (opsional).
- Konfirmasi reservasi; cek `reservations.special_requests` berisi daftar dipisah titik koma.
- Step 3/4: total menghormati `BOOKING_TAX_RATE` dan `BOOKING_SERVICE_FEE` jika diubah di `.env`.

## Detail Kamar
- Isi `rooms.personalized_facilities` dengan array JSON (mis. ["Welcome fruit","Kid amenities"]).
- Buka halaman detail kamar; badge “Fasilitas Personalisasi” muncul sesuai data.

## Admin User Management (First-Party Data)
- Edit pengguna: isi Nama Lengkap, Telepon, Alamat, Kota, Provinsi, Tanggal Lahir, Jenis Kelamin, Tujuan Menginap.
- Di daftar user, kolom Kota/Provinsi, Gender, dan Umur (dari tanggal lahir) tampil benar dan tersimpan.

## Admin Reservasi
- Di tabel reservasi, kolom “Permintaan” tampil (teks terpotong); arahkan kursor untuk melihat lengkap (tooltip).
- Di modal, `special_requests` dapat disimpan tanpa error.

## Resend Verifikasi Email (Throttle)
- Panggil `/email/resend` hingga 3 kali dalam 1 menit; panggilan ke-4 harus ditolak (HTTP 429).

## Ekspor CSV
- Ekspor Users/Rooms/Reservations/Bills/Contacts berisi nilai yang sudah disanitasi (karakter awal `=`, `+`, `-`, `@` diprefix `'`).
- Buka di spreadsheet dan pastikan tidak ada formula yang dieksekusi.

## Keamanan & Operasional
- Pastikan file `.env` tidak ada di repo; lakukan rotasi kredensial dan pembersihan riwayat secara terpisah.
- Pastikan `public/storage` tidak ditrack; jalankan `php artisan storage:link` di lingkungan yang butuh.
- Admin: Kelola Inventori Barang per Kamar
  - Akses: Admin → Barang per Kamar (`/admin/room-items`).
  - Filter berdasarkan Tipe Kamar dan Kamar; cari nama item.
  - Tambah item (Nama, Jumlah, Satuan) untuk kamar tertentu; Edit/Hapus item.
  - Pastikan pagination dan filter tetap konsisten saat berpindah halaman.
