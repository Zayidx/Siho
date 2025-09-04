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
use App\Models\RoomImage;
use App\Models\HotelGallery;
use App\Models\RoomItemInventory;
use App\Models\MenuCategory;
use App\Models\MenuItem;
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
        // SEEDER: INVENTORI PER KAMAR (AMENITIES)
        // =================================================================
        $this->command->info('Mengisi inventory barang per kamar...');
        $amenitySets = function (RoomType $type) {
            // Atur default berdasarkan kapasitas tipe kamar
            $beds = max(1, (int) $type->capacity);
            $shampoo = $beds; // 1 per tamu
            $soap = $beds;    // 1 per tamu
            return [
                ['name' => 'Kasur', 'quantity' => $beds, 'unit' => 'buah'],
                ['name' => 'Keset', 'quantity' => 1, 'unit' => 'buah'],
                ['name' => 'Shampoo', 'quantity' => $shampoo, 'unit' => 'botol'],
                ['name' => 'Sabun', 'quantity' => $soap, 'unit' => 'batang'],
                ['name' => 'Handuk', 'quantity' => $beds, 'unit' => 'buah'],
            ];
        };
        $rooms = Rooms::with('roomType')->get();
        $bar = $this->command->getOutput()->createProgressBar($rooms->count());
        $bar->start();
        foreach ($rooms as $room) {
            foreach ($amenitySets($room->roomType) as $a) {
                RoomItemInventory::create([
                    'room_id' => $room->id,
                    'name' => $a['name'],
                    'quantity' => $a['quantity'],
                    'unit' => $a['unit'],
                ]);
            }
            $bar->advance();
        }
        $bar->finish();
        $this->command->newLine(2);

        // =================================================================
        // SEEDER: COVER IMAGE UNTUK ROOM TYPE & GALERI HOTEL
        // =================================================================
        $this->command->info('Menambahkan cover image untuk tipe kamar & galeri hotel...');
        $seedImagePath = 'assets/image.png'; // file web: public/storage/assets/image.png

        foreach (RoomType::all() as $type) {
            // set satu cover per tipe
            RoomImage::create([
                'room_type_id' => $type->id,
                'path' => $seedImagePath,
                'sort_order' => 1,
                'is_cover' => true,
            ]);
        }

        // galeri: isi 5 kategori minimal 1 gambar
        foreach (['facade','facilities','public','restaurant','room'] as $idx => $cat) {
            HotelGallery::create([
                'path' => $seedImagePath,
                'category' => $cat,
                'sort_order' => $idx + 1,
                'is_cover' => $cat === 'facade',
            ]);
        }
        $this->command->info('Seed gambar selesai.');


        // =================================================================
        // SEEDER: RESTORAN (F&B) — KATEGORI & ITEM MENU
        // Menggunakan gambar seed yang sama (assets/image.png)
        // =================================================================
        $this->command->info('Menambahkan data menu restoran (F&B)...');
        $menuCategories = [
            'Makanan', 'Minuman', 'Dessert'
        ];
        $catIds = [];
        foreach ($menuCategories as $cname) {
            $cat = MenuCategory::firstOrCreate(['name' => $cname], [
                'description' => null,
                'is_active' => true,
            ]);
            $catIds[$cname] = $cat->id;
        }

        $menuItems = [
            ['cat' => 'Makanan', 'name' => 'Nasi Goreng Spesial', 'price' => 45000, 'popular' => true],
            ['cat' => 'Makanan', 'name' => 'Mie Goreng Ayam', 'price' => 38000, 'popular' => true],
            ['cat' => 'Makanan', 'name' => 'Sate Ayam', 'price' => 50000, 'popular' => false],
            ['cat' => 'Minuman', 'name' => 'Es Teh Manis', 'price' => 15000, 'popular' => true],
            ['cat' => 'Minuman', 'name' => 'Kopi Susu', 'price' => 28000, 'popular' => false],
            ['cat' => 'Dessert', 'name' => 'Puding Coklat', 'price' => 25000, 'popular' => false],
            ['cat' => 'Dessert', 'name' => 'Cheesecake', 'price' => 30000, 'popular' => true],
        ];

        foreach ($menuItems as $row) {
            MenuItem::firstOrCreate([
                'menu_category_id' => $catIds[$row['cat']] ?? array_values($catIds)[0],
                'name' => $row['name'],
            ], [
                'description' => 'Menu favorit hotel kami.',
                'price' => $row['price'],
                'is_active' => true,
                'is_popular' => $row['popular'],
                'image' => $seedImagePath,
            ]);
        }
        $this->command->info('Data menu restoran selesai ditambahkan.');


        // =================================================================
        // SEEDER UNTUK USERS (sebagai tamu)
        // =================================================================
        $this->command->info('Membuat data dummy untuk users (tamu)...');
        $totalGuests = 10;
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
                'id_number' => $faker->unique()->numerify('################'),
                'foto' => null,
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
