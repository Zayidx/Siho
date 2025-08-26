<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Your Booking - Grand Luxe Hotel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
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
<body class="bg-muted text-foreground font-sans antialiased">

    <!-- Header -->
    <header id="main-header" class="sticky top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-border">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-20 max-w-7xl">
            <a href="/" class="text-2xl font-bold text-primary font-serif">Grand Luxe</a>
            <div class="flex items-center space-x-4">
                 @auth
                    <a href="{{ route('user.dashboard') }}" class="font-semibold text-primary">My Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="font-semibold text-primary">Login</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 max-w-4xl">
        <h1 class="text-4xl font-bold font-serif text-center mb-8">Confirm Your Reservation</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- Left Column: Booking Details -->
            <div class="md:col-span-2">
                <div class="bg-card p-8 rounded-xl shadow-lg border border-border">
                    <h2 class="text-2xl font-bold font-serif mb-6">Your Details</h2>
                    
                    @auth
                        <div class="mb-4">
                            <p class="text-muted-foreground">You are booking as:</p>
                            <p class="text-lg font-semibold text-primary">{{ Auth::user()->name }} ({{ Auth::user()->email }})</p>
                        </div>
                        <div class="mt-6">
                            <label for="special_requests" class="block text-sm font-medium text-foreground">Special Requests (Optional)</label>
                            <textarea id="special_requests" wire:model="special_requests" rows="4" class="mt-1 block w-full bg-muted border-border rounded-md p-3 focus:ring-2 focus:ring-secondary focus:border-transparent" placeholder="e.g., high floor, away from elevator..."></textarea>
                        </div>
                    @else
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md">
                            <p class="font-bold">Action Required</p>
                            <p>Please <a href="{{ route('login') }}" class="font-bold underline">Login</a> or <a href="{{ route('register') }}" class="font-bold underline">Register</a> to complete your booking.</p>
                        </div>
                    @endguest

                </div>
            </div>

            <!-- Right Column: Summary -->
            <div class="md:col-span-1">
                <div class="bg-card p-6 rounded-xl shadow-lg border border-border sticky top-28">
                    <h3 class="text-xl font-bold font-serif mb-4 border-b pb-3">Booking Summary</h3>
                    
                    <div>
                        <img src="https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=80" alt="{{ $room->room_type }}" class="w-full h-40 object-cover rounded-lg mb-4">
                        <h4 class="font-bold text-lg">{{ $room->room_type }} - No. {{ $room->room_number }}</h4>
                    </div>

                    <div class="mt-4 space-y-2 text-sm text-muted-foreground">
                        <div class="flex justify-between">
                            <span>Check-in</span>
                            <span class="font-semibold text-foreground">{{ Carbon\Carbon::parse($checkinDate)->format('D, M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Check-out</span>
                            <span class="font-semibold text-foreground">{{ Carbon\Carbon::parse($checkoutDate)->format('D, M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Total Nights</span>
                            <span class="font-semibold text-foreground">{{ $nights }}</span>
                        </div>
                         <div class="flex justify-between">
                            <span>Guests</span>
                            <span class="font-semibold text-foreground">{{ $guests }}</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold">Total Price</span>
                            <span class="text-2xl font-bold text-secondary font-serif">Rp{{ number_format($totalPrice, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        @auth
                            <button wire:click="confirmBooking" class="w-full bg-secondary hover:bg-secondary/90 text-secondary-foreground px-8 py-3 rounded-full text-lg font-semibold transition-all transform hover:scale-105 shadow-lg">
                                Confirm & Book
                            </button>
                        @else
                             <button class="w-full bg-gray-400 text-gray-100 px-8 py-3 rounded-full text-lg font-semibold cursor-not-allowed" disabled>
                                Login to Book
                            </button>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </main>

    @livewireScripts
</body>
</html>