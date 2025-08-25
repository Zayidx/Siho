<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rooms;
use App\Models\Guest;
use App\Models\Reservations;
use App\Models\Bills;
use Carbon\Carbon;
use Faker\Factory as Faker;

class HotelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // =================================================================
        // SEEDER UNTUK KAMAR (ROOMS) - Tidak ada perubahan
        // =================================================================
        $this->command->info('Membuat data dummy untuk kamar...');
        $roomTypes = ['Standard', 'Deluxe', 'Suite'];
        $generatedRoomNumbers = [];
        $totalRooms = 500;

        $bar = $this->command->getOutput()->createProgressBar($totalRooms);
        $bar->start();

        for ($i = 0; $i < $totalRooms; $i++) {
            $isUnique = false;
            $roomNumber = '';

            while (!$isUnique) {
                $floor = $faker->numberBetween(1, 30);
                $numberInFloor = $faker->numberBetween(1, 25);
                $potentialRoomNumber = $floor . str_pad($numberInFloor, 2, '0', STR_PAD_LEFT);
                
                if (!in_array($potentialRoomNumber, $generatedRoomNumbers)) {
                    $roomNumber = $potentialRoomNumber;
                    $generatedRoomNumbers[] = $roomNumber;
                    $isUnique = true;
                }
            }

            $type = $faker->randomElement($roomTypes);
            $price = 0;
            if ($type == 'Standard') $price = $faker->numberBetween(500000, 800000);
            if ($type == 'Deluxe') $price = $faker->numberBetween(800000, 1500000);
            if ($type == 'Suite') $price = $faker->numberBetween(1500000, 3000000);

            Rooms::create([
                'room_number' => $roomNumber,
                'room_type' => $type,
                'status' => 'Available',
                'floor' => $floor,
                'description' => $faker->paragraph,
                'price_per_night' => $price,
            ]);

            $bar->advance();
        }
        $bar->finish();
        $this->command->newLine(2);


        // =================================================================
        // SEEDER UNTUK TAMU (GUESTS) - Tidak ada perubahan
        // =================================================================
        $this->command->info('Membuat data dummy untuk tamu...');
        $totalGuests = 1000;
        $bar = $this->command->getOutput()->createProgressBar($totalGuests);
        $bar->start();

        for ($i = 0; $i < $totalGuests; $i++) {
            Guest::create([
                'full_name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'id_number' => $faker->nik(),
                'photo' => null,
                'date_of_birth' => $faker->date(),
            ]);
            $bar->advance();
        }
        $bar->finish();
        $this->command->newLine(2);


        // =================================================================
        // SEEDER UNTUK RESERVASI & TAGIHAN (RESERVATIONS & BILLS)
        // =================================================================
        $this->command->info('Membuat data dummy untuk reservasi dan tagihan...');
        $guestIds = Guest::pluck('id')->toArray();
        $allRoomIds = Rooms::pluck('id')->toArray(); // [PERUBAHAN] Ambil semua ID kamar sekali saja
        $paymentMethods = ['Credit Card', 'Cash', 'Bank Transfer'];
        $totalReservations = 800;
        $bar = $this->command->getOutput()->createProgressBar($totalReservations);
        $bar->start();

        for ($i = 0; $i < $totalReservations; $i++) {
            $checkIn = Carbon::instance($faker->dateTimeBetween('-1 year', '+3 months'));
            
            $nights = ($i % 10 === 0) ? 7 : $faker->numberBetween(1, 10);
            $checkOut = $checkIn->copy()->addDays($nights);

            $status = 'Confirmed';
            if ($checkOut->isPast()) {
                $status = 'Completed';
            } elseif ($checkIn->isPast() || $checkIn->isToday()) {
                $status = 'Checked-in';
            }

            $reservation = Reservations::create([
                'guest_id' => $faker->randomElement($guestIds),
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'status' => $status,
                'special_requests' => $faker->boolean(20) ? $faker->sentence : null,
            ]);

            $numberOfRooms = $faker->numberBetween(1, 3);
            // [PERUBAHAN] Pilih kamar secara acak tanpa melihat statusnya saat ini
            $selectedRoomIds = (array) $faker->randomElements($allRoomIds, $numberOfRooms);
            $reservation->rooms()->attach($selectedRoomIds);
            
            // [PERUBAHAN] Semua logika pembaruan status kamar dihapus dari dalam loop ini

            // Buat tagihan jika statusnya 'Completed'
            if ($status == 'Completed' && $faker->boolean(90)) {
                $totalAmount = Rooms::whereIn('id', $selectedRoomIds)->sum('price_per_night') * $nights;
                Bills::create([
                    'reservation_id' => $reservation->id,
                    'total_amount' => $totalAmount,
                    'issued_at' => $checkOut,
                    'paid_at' => $checkOut->copy()->addHours($faker->numberBetween(1, 3)),
                    'payment_method' => $faker->randomElement($paymentMethods),
                ]);
            }
            
            $bar->advance();
        }
        $bar->finish();
        $this->command->newLine(2);

        // =================================================================
        // [PERUBAHAN] LOGIKA BARU: SET STATUS KAMAR SETELAH SEMUA RESERVASI DIBUAT
        // =================================================================
        $this->command->info('Menyesuaikan status kamar berdasarkan reservasi saat ini...');
        
        // 1. Reset semua status kamar menjadi 'Available' sebagai dasar
        Rooms::query()->update(['status' => 'Available']);

        // 2. Dapatkan semua ID kamar yang terhubung dengan reservasi 'Checked-in'
        $occupiedRoomIds = Reservations::where('status', 'Checked-in')
            ->with('rooms') // Eager load relasi rooms
            ->get()
            ->pluck('rooms.*.id') // Ambil ID dari semua kamar yang terhubung
            ->flatten() // Ratakan collection jika ada reservasi dengan banyak kamar
            ->unique(); // Pastikan ID kamar unik

        // 3. Update status kamar-kamar tersebut menjadi 'Occupied'
        if ($occupiedRoomIds->isNotEmpty()) {
            Rooms::whereIn('id', $occupiedRoomIds)->update(['status' => 'Occupied']);
            $this->command->info($occupiedRoomIds->count() . ' kamar ditandai sebagai "Occupied".');
        }

        // 4. Atur beberapa kamar yang tersedia menjadi 'Cleaning'
        $availableRooms = Rooms::where('status', 'Available')->get();
        if ($availableRooms->count() > 20) { // Cek jika ada cukup kamar untuk diacak
            $roomsToCleanCount = floor($availableRooms->count() * 0.15); // 15% dari sisa kamar
            $roomsToCleanIds = $availableRooms->random($roomsToCleanCount)->pluck('id');
            
            Rooms::whereIn('id', $roomsToCleanIds)->update(['status' => 'Cleaning']);
            $this->command->info($roomsToCleanCount . ' kamar ditandai sebagai "Cleaning".');
        }
        
        $this->command->info('Status kamar akhir berhasil diatur.');
        $this->command->info('Semua data dummy berhasil dibuat.');
    }
}
