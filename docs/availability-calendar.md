# Availability Calendar

Dokumen ini menjelaskan konsep, arsitektur, dan cara kerja fitur Availability Calendar (kalender ketersediaan kamar) di panel admin, termasuk teknologi yang dipakai serta panduan implementasi dan troubleshooting.

## Ringkasan Fitur
- Menampilkan reservasi pada kalender bulanan/mingguan/harian (FullCalendar).
- Filter real‑time: status reservasi, tipe kamar, dan nomor kamar.
- Warna event berdasarkan status (Confirmed, Checked‑in, Completed, Cancelled).
- Klik event menampilkan detail ringkas (overlay) seperti nomor kamar, tamu, tanggal, status.

## Teknologi yang Digunakan
- Backend: Laravel + Eloquent.
- UI reaktif: Livewire (komponen `AvailabilityCalendar`).
- Kalender: FullCalendar v6 (via CDN).
- Data diambil via endpoint JSON (route `admin.calendar.events`) yang memanggil method `getCalendarEvents` di komponen Livewire.

## Arsitektur dan Alur Data
1. Admin membuka halaman kalender: `GET /admin/availability-calendar` → `App\Livewire\Admin\AvailabilityCalendar`.
2. View memuat FullCalendar (CDN) dan menginisialisasi kalender di `#calendar`.
3. FullCalendar memanggil endpoint event dengan parameter `start` dan `end` (serta filter dari Livewire) untuk mengambil data event.
4. Method `getCalendarEvents` melakukan query ke model `Reservations` beserta relasi `rooms` dan `guest`, lalu memetakan setiap reservasi menjadi event FullCalendar.
5. Ketika filter Livewire berubah, komponen mengirim event browser `calendar:filters-updated`, dan kalender memanggil ulang endpoint events.

## Struktur Kode (File Penting)
- Komponen: `app/Livewire/Admin/AvailabilityCalendar.php`
- View: `resources/views/livewire/admin/availability-calendar.blade.php`
- Rute: didefinisikan di `routes/web.php`
  - `Route::get('/admin/availability-calendar', AvailabilityCalendar::class)->name('admin.availability.calendar');`
  - `Route::get('/admin/calendar-events', [AvailabilityCalendar::class, 'getCalendarEvents'])->name('admin.calendar.events');`
- Model terkait: `App\Models\Reservations` (relasi `rooms`, `guest`), `App\Models\Rooms`, `App\Models\RoomType`.

## Detail Implementasi
- Komponen Livewire menyajikan daftar `roomTypes` ke view untuk filter.
- Properti Livewire untuk filter:
  - `status` (string), `roomType` (id atau kosong), `roomNumber` (string).
- Endpoint events (`getCalendarEvents`):
  - Validasi `start` dan `end` wajib (format tanggal).
  - Query `Reservations` berdasarkan rentang tanggal dan filter opsional (status, room_type_id, nomor kamar dengan `like`).
  - Setiap reservasi yang memiliki banyak kamar akan dipecah menjadi beberapa event (1 event per kamar).
  - Penentuan warna (background dan border) berdasarkan status (case‑insensitive).
  - Format tanggal: `start` dan `end` menggunakan `toDateString()`; FullCalendar memperlakukan `end` sebagai eksklusif.

Contoh payload event (per item):
```json
{
  "title": "Kamar 101",
  "start": "2025-09-01",
  "end": "2025-09-03",
  "allDay": true,
  "backgroundColor": "#0d6efd",
  "borderColor": "#0d6efd",
  "textColor": "#fff",
  "extendedProps": {
    "reservation_id": 123,
    "guest": "Budi Santoso",
    "room": "101",
    "status": "Confirmed",
    "check_in": "2025-09-01",
    "check_out": "2025-09-03"
  }
}
```

## Cara Menambahkan/Integrasi
1. Rute:
   - Pastikan dua rute admin aktif (lihat bagian Struktur Kode). Proteksi dengan middleware `auth` dan `role:superadmin`.
2. Komponen Livewire:
   - File sudah tersedia. Jika menyalin ke project lain, tempatkan pada `app/Livewire/Admin/AvailabilityCalendar.php`.
3. View:
   - Gunakan view yang ada atau sesuaikan. Pastikan menyertakan CDN FullCalendar:
     - `https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js`
   - Elemen `#calendar` harus memiliki `wire:ignore` agar FullCalendar tidak bentrok dengan DOM diff Livewire.
4. Relasi Model:
   - `Reservations` harus memiliki relasi `rooms()` (banyak) dan `guest()` (satu ke `User`).
   - Kolom tanggal: `check_in_date`, `check_out_date` harus bertipe tanggal dan di-cast ke Carbon.
5. Filter:
   - View mengikat filter ke properti Livewire (`wire:model.live`) dan memanggil `filtersUpdated()` untuk me‑refetch event.

## Penanganan Status dan Warna
- Pemetaan warna di komponen:
  - Confirmed: biru `#0d6efd`
  - Checked‑in: hijau `#198754`
  - Completed: abu‑abu `#6c757d`
  - Cancelled: merah `#dc3545`
  - Selain itu: default biru tua `#1a2e44`
- Anda dapat menyesuaikan peta warna pada komponen di bagian `$colors`.

## Keamanan & Kinerja
- Endpoint events mengembalikan hanya data yang dibutuhkan FullCalendar (minim payload) untuk performa.
- Pastikan rute hanya dapat diakses oleh admin (role `superadmin`).
- Gunakan eager loading (`with(['rooms','guest'])`) untuk menghindari N+1 queries.

## Troubleshooting
- Kalender tidak menampilkan event:
  - Periksa error di console browser (CORS/JS). Pastikan rute `admin.calendar.events` bisa diakses (autentikasi benar).
  - Pastikan data `Reservations` berada dalam rentang tanggal yang diminta.
- Tanggal bergeser satu hari:
  - Ingat FullCalendar menganggap `end` eksklusif. Pastikan `check_out_date` sudah sesuai atau tambahkan satu hari jika ingin tampilan inklusif.
- Filter tidak bereaksi:
  - Pastikan event browser `calendar:filters-updated` terkirim (lihat `filtersUpdated()` pada komponen) dan handler `window.addEventListener('calendar:filters-updated', ...)` ada di view.
- Performa lambat:
  - Tambahkan indeks DB pada kolom tanggal atau status bila perlu. Pertimbangkan pagination server‑side bila dataset sangat besar (custom source).

## Pengembangan Lanjutan (Opsional)
- Drag‑drop untuk reschedule langsung di kalender (update `Reservations`).
- Tooltip dengan detail tamu/biaya.
- Penandaan maintenance/cleaning sebagai event khusus.
- Multi‑resource view (per tipe kamar atau per lantai) menggunakan FullCalendar resource view.

---

Referensi kode:
- `app/Livewire/Admin/AvailabilityCalendar.php`
- `resources/views/livewire/admin/availability-calendar.blade.php`
- `routes/web.php` (rute admin dan events)
