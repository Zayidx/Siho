<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Stay - Grand Luxe Hotel</title>
    <!-- Google Fonts: Lora for headings, Lato for body text -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Custom Tailwind CSS configuration from welcome page
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': { DEFAULT: '#1a2e44', foreground: '#f0f0f0' },
                        'secondary': { DEFAULT: '#c5a57e', foreground: '#1a2e44' },
                        'background': '#fdfdfd',
                        'foreground': '#1f2937',
                        'card': '#ffffff',
                        'muted': { DEFAULT: '#f3f4f6', foreground: '#6b7280' },
                        'border': '#e5e7eb',
                    },
                    fontFamily: {
                        'serif': ['"Lora"', 'serif'],
                        'sans': ['"Lato"', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    @livewireStyles
</head>
<body class="bg-background text-foreground font-sans antialiased">

    <!-- Header -->
    <header id="main-header" class="sticky top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-border">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-20 max-w-7xl">
            <a href="/" class="text-2xl font-bold text-primary font-serif">Grand Luxe</a>
            <nav class="hidden md:flex items-center space-x-8 font-medium">
                <a href="/#rooms" class="text-slate-600 hover:text-secondary transition-colors">Rooms</a>
                <a href="/#amenities" class="text-slate-600 hover:text-secondary transition-colors">Amenities</a>
                <a href="{{ route('booking') }}" class="font-semibold text-secondary">Book Now</a>
            </nav>
            <div class="flex items-center space-x-4">
                @auth
                    <a href="{{ route('user.dashboard') }}" class="bg-secondary hover:bg-secondary/90 text-secondary-foreground px-6 py-2.5 rounded-full font-semibold transition-all transform hover:scale-105 shadow-sm">My Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="bg-secondary hover:bg-secondary/90 text-secondary-foreground px-6 py-2.5 rounded-full font-semibold transition-all transform hover:scale-105 shadow-sm">Login</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 max-w-7xl">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold font-serif text-center mb-8">Find Your Perfect Room</h1>

            <!-- Booking Search Form -->
            <div class="bg-card p-8 rounded-xl shadow-lg border border-border">
                <form wire:submit.prevent="searchRooms" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    <!-- Check-in Date -->
                    <div>
                        <label for="checkin" class="block text-sm font-medium text-foreground">Check-in Date</label>
                        <input type="date" id="checkin" wire:model="checkinDate" class="mt-1 block w-full bg-muted border-border rounded-md py-2 px-3 focus:ring-2 focus:ring-secondary focus:border-transparent">
                    </div>

                    <!-- Check-out Date -->
                    <div>
                        <label for="checkout" class="block text-sm font-medium text-foreground">Check-out Date</label>
                        <input type="date" id="checkout" wire:model="checkoutDate" class="mt-1 block w-full bg-muted border-border rounded-md py-2 px-3 focus:ring-2 focus:ring-secondary focus:border-transparent">
                    </div>

                    <!-- Number of Guests -->
                    <div>
                        <label for="guests" class="block text-sm font-medium text-foreground">Guests</label>
                        <select id="guests" wire:model="guests" class="mt-1 block w-full bg-muted border-border rounded-md py-2 px-3 focus:ring-2 focus:ring-secondary focus:border-transparent">
                            <option>1</option>
                            <option>2</option>
                            <option>3</option>
                            <option>4</option>
                        </select>
                    </div>

                    <!-- Search Button -->
                    <div class="md:col-span-3 text-center mt-4">
                        <button type="submit" class="w-full md:w-auto bg-secondary hover:bg-secondary/90 text-secondary-foreground px-12 py-3 rounded-full font-semibold transition-transform transform hover:scale-105 shadow-lg">
                            Search Available Rooms
                        </button>
                    </div>
                </form>
            </div>

            <!-- Search Results Section -->
            <div class="mt-12">
                <div wire:loading class="text-center text-muted-foreground py-8">
                    <p>Searching for rooms...</p>
                </div>

                <div wire:loading.remove>
                    @if ($availableRooms !== null)
                        <h2 class="text-2xl font-bold font-serif mb-6">Available Rooms</h2>
                        @forelse ($availableRooms as $room)
                            <div class="bg-card border border-border rounded-xl overflow-hidden shadow-lg mb-6 flex flex-col md:flex-row">
                                <div class="md:w-1/3">
                                    {{-- Placeholder image --}}
                                    <img src="https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=80" alt="{{ $room->room_type }}" class="w-full h-64 md:h-full object-cover">
                                </div>
                                <div class="p-6 flex flex-col flex-grow md:w-2/3">
                                    <h3 class="text-2xl font-bold font-serif mb-2 text-primary">{{ $room->room_type }} - No. {{ $room->room_number }}</h3>
                                    <p class="text-muted-foreground mb-4 flex-grow">{{ $room->description }}</p>
                                    <div class="flex items-center justify-between mt-4">
                                        <span class="text-2xl font-bold text-secondary font-serif">Rp{{ number_format($room->price_per_night, 2, ',', '.') }}<span class="text-sm font-sans text-muted-foreground">/night</span></span>
                                        <button wire:click="bookRoom({{ $room->id }})" class="bg-primary hover:bg-primary/90 text-primary-foreground px-6 py-3 rounded-full font-semibold transition-transform transform hover:scale-105">Book Now</button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted-foreground py-8 border border-dashed rounded-lg">
                                <p>No available rooms found for the selected dates.</p>
                            </div>
                        @endforelse
                    @else
                        <div class="text-center text-muted-foreground py-8">
                            <p>Please select your dates to see available rooms.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-muted border-t border-border py-12 mt-16">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl text-center">
            <p class="text-muted-foreground">© 2024 Grand Luxe. All rights reserved.</p>
        </div>
    </footer>

    @livewireScripts
</body>
</html>