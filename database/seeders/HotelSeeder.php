<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rooms;
use App\Models\User;
use App\Models\Role;
use App\Models\RoomType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
        // SEEDER UNTUK TIPE KAMAR & KAMAR (ROOM TYPES & ROOMS)
        // =================================================================
        $this->command->info('Membuat data dummy untuk tipe kamar...');
        $roomTypesData = [
            ['name' => 'Standard', 'description' => 'A comfortable room with all the basic amenities.', 'base_price' => 750000, 'capacity' => 2],
            ['name' => 'Deluxe', 'description' => 'A more spacious room with premium amenities and a better view.', 'base_price' => 1200000, 'capacity' => 2],
            ['name' => 'Suite', 'description' => 'A luxurious suite with a separate living area and top-tier services.', 'base_price' => 2500000, 'capacity' => 4],
        ];

        foreach ($roomTypesData as $type) {
            RoomType::create($type);
        }
        $this->command->info('Tipe kamar berhasil dibuat.');

        $this->command->info('Membuat data dummy untuk kamar...');
        $createdRoomTypes = RoomType::all();
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

            $roomType = $createdRoomTypes->random();

            Rooms::create([
                'room_number' => $roomNumber,
                'room_type_id' => $roomType->id,
                'status' => 'Available',
                'floor' => $floor,
                'description' => $faker->paragraph,
                'price_per_night' => $roomType->base_price,
            ]);

            $bar->advance();
        }
        $bar->finish();
        $this->command->newLine(2);


        // =================================================================
        // SEEDER UNTUK USERS (sebagai tamu)
        // =================================================================
        $this->command->info('Membuat data dummy untuk users (tamu)...');
        $totalGuests = 1000;
        $bar = $this->command->getOutput()->createProgressBar($totalGuests);
        $bar->start();

        for ($i = 0; $i < $totalGuests; $i++) {
            $fullName = $faker->name;

            $base = Str::slug($fullName);
            if (!$base) {
                $base = $faker->unique()->userName();
            }
            $username = $base;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $base . $counter++;
            }

            User::create([
                'full_name' => $fullName,
                'username' => $username,
                'email' => $faker->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'role_id' => Role::where('name', 'users')->value('id') ?? 2,
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'id_number' => $faker->unique()->nik(),
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
        $guestIds = User::pluck('id')->toArray();
        $allRoomIds = Rooms::pluck('id')->toArray();
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
            $selectedRoomIds = (array) $faker->randomElements($allRoomIds, $numberOfRooms);
            $reservation->rooms()->attach($selectedRoomIds);
            
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
        // LOGIKA BARU: SET STATUS KAMAR SETELAH SEMUA RESERVASI DIBUAT
        // =================================================================
        $this->command->info('Menyesuaikan status kamar berdasarkan reservasi saat ini...');
        
        Rooms::query()->update(['status' => 'Available']);

        $occupiedRoomIds = Reservations::where('status', 'Checked-in')
            ->with('rooms')
            ->get()
            ->pluck('rooms.*.id')
            ->flatten()
            ->unique();

        if ($occupiedRoomIds->isNotEmpty()) {
            Rooms::whereIn('id', $occupiedRoomIds)->update(['status' => 'Occupied']);
            $this->command->info($occupiedRoomIds->count() . ' kamar ditandai sebagai "Occupied".');
        }

        $availableRooms = Rooms::where('status', 'Available')->get();
        if ($availableRooms->count() > 20) {
            $roomsToCleanCount = floor($availableRooms->count() * 0.15);
            $roomsToCleanIds = $availableRooms->random($roomsToCleanCount)->pluck('id');
            
            Rooms::whereIn('id', $roomsToCleanIds)->update(['status' => 'Cleaning']);
            $this->command->info($roomsToCleanCount . ' kamar ditandai sebagai "Cleaning".');
        }
        
        $this->command->info('Status kamar akhir berhasil diatur.');
        $this->command->info('Semua data dummy berhasil dibuat.');
    }
}