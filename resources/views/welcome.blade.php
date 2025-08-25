<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grand Luxe Hotel - Welcome</title>
    <!-- Google Fonts: Lora for headings, Lato for body text -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Feather Icons for cleaner icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    <!-- AOS (Animate On Scroll) Library CSS -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <script>
        // Custom Tailwind CSS configuration for a more luxurious feel
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'primary': {
                            DEFAULT: '#1a2e44', // Deeper navy blue
                            foreground: '#f0f0f0',
                        },
                        'secondary': {
                            DEFAULT: '#c5a57e', // Soft gold/bronze
                            foreground: '#1a2e44',
                        },
                        'background': '#fdfdfd', // Almost white
                        'foreground': '#1f2937', // Dark slate
                        'card': '#ffffff',
                        'muted': {
                            DEFAULT: '#f3f4f6',
                            foreground: '#6b7280',
                        },
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
    <style>
        /* Global transition for smooth theme switching */
        body, header, footer, section, div {
            transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out, border-color 0.3s ease-in-out;
        }
        .dark body { background-color: #0f172a; }

        /* Parallax effect for hero background */
        .hero-bg {
            background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        
        /* Style for Gallery overlay and modal */
        .gallery-item .overlay {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        .gallery-item:hover .overlay { opacity: 1; }
        #gallery-modal.hidden { display: none; }
        
        /* Custom styles for date picker icon */
        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            filter: invert(0.8) brightness(1.2);
        }
        .dark input[type="date"]::-webkit-calendar-picker-indicator {
             filter: invert(1) brightness(0.8);
        }

        /* Header shadow on scroll */
        .header-scrolled {
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
    </style>
</head>
<body class="bg-background dark:bg-slate-900 text-foreground dark:text-slate-200 font-sans antialiased">
    
    <!-- Header -->
    <header id="main-header" class="sticky top-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg transition-shadow duration-300">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-20 max-w-7xl">
            <a href="#" class="text-2xl font-bold text-primary dark:text-secondary font-serif">Grand Luxe</a>
            
            <nav class="hidden md:flex items-center space-x-8 font-medium">
                <a href="#rooms" class="text-slate-600 dark:text-slate-300 hover:text-secondary dark:hover:text-secondary transition-colors">Rooms</a>
                <a href="#amenities" class="text-slate-600 dark:text-slate-300 hover:text-secondary dark:hover:text-secondary transition-colors">Amenities</a>
                <a href="#gallery" class="text-slate-600 dark:text-slate-300 hover:text-secondary dark:hover:text-secondary transition-colors">Gallery</a>
                <a href="#offers" class="text-slate-600 dark:text-slate-300 hover:text-secondary dark:hover:text-secondary transition-colors">Offers</a>
                <a href="#contact" class="text-slate-600 dark:text-slate-300 hover:text-secondary dark:hover:text-secondary transition-colors">Contact</a>
            </nav>

            <div class="flex items-center space-x-2 sm:space-x-4">
                <button id="theme-toggle" aria-label="Toggle theme" class="p-2 rounded-full hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    <i data-feather="sun" class="sun-icon text-slate-700 dark:hidden"></i>
                    <i data-feather="moon" class="moon-icon text-slate-300 hidden dark:block"></i>
                </button>
                <a href="{{ route('login') }}" class="hidden sm:inline-block bg-secondary hover:bg-secondary/90 text-secondary-foreground px-6 py-2.5 rounded-full font-semibold transition-all transform hover:scale-105 shadow-sm">
                    Book Now
                </a>
                <button id="mobile-menu-button" aria-label="Open menu" class="md:hidden p-2 rounded-full hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    <i data-feather="menu" class="text-slate-700 dark:text-slate-300"></i>
                </button>
            </div>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-white dark:bg-slate-900 border-t border-border dark:border-slate-800">
            <nav class="flex flex-col items-center space-y-4 py-4">
                <a href="#rooms" class="mobile-link text-slate-600 dark:text-slate-300">Rooms</a>
                <a href="#amenities" class="mobile-link text-slate-600 dark:text-slate-300">Amenities</a>
                <a href="#gallery" class="mobile-link text-slate-600 dark:text-slate-300">Gallery</a>
                <a href="#offers" class="mobile-link text-slate-600 dark:text-slate-300">Offers</a>
                <a href="#contact" class="mobile-link text-slate-600 dark:text-slate-300">Contact</a>
                 <a href="#booking-form" class="mobile-link bg-secondary text-secondary-foreground px-8 py-3 rounded-full font-semibold mt-4 w-3/4 text-center">
                    Book Now
                </a>
            </nav>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero-bg min-h-screen flex items-center justify-center text-center text-white p-4">
            <div class="max-w-4xl" data-aos="fade-up">
                <h1 class="text-4xl sm:text-6xl md:text-7xl font-bold font-serif mb-6 leading-tight drop-shadow-lg">
                    Selamat Datang di<br>
                    <span class="text-secondary">Grand Luxe</span>
                </h1>
                <p class="text-lg md:text-xl mb-12 text-slate-200 max-w-2xl mx-auto leading-relaxed drop-shadow-md">
                    Nikmati pengalaman menginap yang tak terlupakan dengan layanan bintang lima dan kemewahan yang tiada tara.
                </p>
                
                <!-- Booking Form in Hero -->
                <div id="booking-form" class="bg-black/20 backdrop-blur-md p-4 sm:p-6 rounded-xl max-w-4xl mx-auto border border-white/20" data-aos="fade-up" data-aos-delay="200">
                    <form class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                        <div class="text-left"><label for="checkin" class="block text-sm font-medium text-white">Check-in</label><input type="date" id="checkin" name="checkin" class="mt-1 block w-full bg-white/20 border-transparent rounded-md py-2 px-3 text-white focus:ring-2 focus:ring-secondary focus:border-transparent"></div>
                        <div class="text-left"><label for="checkout" class="block text-sm font-medium text-white">Check-out</label><input type="date" id="checkout" name="checkout" class="mt-1 block w-full bg-white/20 border-transparent rounded-md py-2 px-3 text-white focus:ring-2 focus:ring-secondary focus:border-transparent"></div>
                        <div class="text-left"><label for="adults" class="block text-sm font-medium text-white">Adults</label><select id="adults" name="adults" class="mt-1 block w-full bg-white/20 border-transparent rounded-md py-2 px-3 text-white focus:ring-2 focus:ring-secondary focus:border-transparent"><option>1</option><option selected>2</option><option>3</option><option>4</option></select></div>
                        <div class="text-left"><label for="children" class="block text-sm font-medium text-white">Children</label><select id="children" name="children" class="mt-1 block w-full bg-white/20 border-transparent rounded-md py-2 px-3 text-white focus:ring-2 focus:ring-secondary focus:border-transparent"><option selected>0</option><option>1</option><option>2</option><option>3</option></select></div>
                        <button type="submit" class="w-full bg-secondary hover:bg-secondary/90 text-secondary-foreground py-2.5 rounded-lg font-semibold transition-transform transform hover:scale-105 lg:col-span-1 h-11">Check Availability</button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Introduction -->
        <section class="py-24 bg-card dark:bg-slate-800">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-3xl" data-aos="fade-up">
                <h2 class="text-4xl font-bold font-serif mb-6 text-foreground dark:text-white">A Haven of <span class="text-secondary">Elegance</span></h2>
                <p class="text-lg text-muted-foreground dark:text-slate-300 leading-relaxed">At Grand Luxe, we believe every moment should be extraordinary. From our meticulously designed suites to our world-class amenities, we create experiences that exceed expectations.</p>
            </div>
        </section>
        
        <!-- Hotel in Numbers Section -->
        <section id="numbers" class="py-20 bg-primary dark:bg-primary/90 text-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                    <div data-aos="fade-up"><h3 class="text-5xl font-bold font-serif number-counter" data-target="1200">0</h3><p class="mt-2 text-primary-foreground/70">Happy Guests Annually</p></div>
                    <div data-aos="fade-up" data-aos-delay="100"><h3 class="text-5xl font-bold font-serif number-counter" data-target="250">0</h3><p class="mt-2 text-primary-foreground/70">Luxury Rooms & Suites</p></div>
                    <div data-aos="fade-up" data-aos-delay="200"><h3 class="text-5xl font-bold font-serif number-counter" data-target="35">0</h3><p class="mt-2 text-primary-foreground/70">Awards Won</p></div>
                    <div data-aos="fade-up" data-aos-delay="300"><h3 class="text-5xl font-bold font-serif number-counter" data-target="150">0</h3><p class="mt-2 text-primary-foreground/70">Expert Staff</p></div>
                </div>
            </div>
        </section>

        <!-- Featured Amenities -->
        <section id="amenities" class="py-24 bg-background dark:bg-slate-900">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
                <h2 class="text-4xl font-bold text-center mb-16 font-serif text-foreground dark:text-white" data-aos="fade-up">Exceptional <span class="text-secondary">Amenities</span></h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-10">
                    <div class="text-center" data-aos="fade-up"><div class="w-20 h-20 bg-secondary/10 dark:bg-secondary/20 rounded-full flex items-center justify-center mx-auto mb-5"><i data-feather="wind" class="w-10 h-10 text-secondary"></i></div><h3 class="text-xl font-bold font-serif mb-2 text-foreground dark:text-white">Infinity Pool</h3><p class="text-muted-foreground dark:text-slate-300">Relax in our rooftop infinity pool with stunning city views.</p></div>
                    <div class="text-center" data-aos="fade-up" data-aos-delay="100"><div class="w-20 h-20 bg-secondary/10 dark:bg-secondary/20 rounded-full flex items-center justify-center mx-auto mb-5"><i data-feather="coffee" class="w-10 h-10 text-secondary"></i></div><h3 class="text-xl font-bold font-serif mb-2 text-foreground dark:text-white">Fine Dining</h3><p class="text-muted-foreground dark:text-slate-300">Award-winning restaurants featuring international cuisine.</p></div>
                    <div class="text-center" data-aos="fade-up" data-aos-delay="200"><div class="w-20 h-20 bg-secondary/10 dark:bg-secondary/20 rounded-full flex items-center justify-center mx-auto mb-5"><i data-feather="activity" class="w-10 h-10 text-secondary"></i></div><h3 class="text-xl font-bold font-serif mb-2 text-foreground dark:text-white">Fitness Center</h3><p class="text-muted-foreground dark:text-slate-300">State-of-the-art equipment and personal training services.</p></div>
                </div>
            </div>
        </section>

        <!-- Room Types Section -->
        <section id="rooms" class="py-24 bg-muted dark:bg-slate-800/50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
                <h2 class="text-4xl font-bold text-center mb-16 font-serif text-foreground dark:text-white" data-aos="fade-up">Luxury <span class="text-secondary">Room Types</span></h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="bg-card dark:bg-slate-800 rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-shadow duration-300" data-aos="fade-up"><img src="https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=800&q=80" alt="Deluxe Room" class="w-full h-64 object-cover"><div class="p-6 flex flex-col"><h3 class="text-2xl font-bold font-serif mb-3 text-foreground dark:text-white">Deluxe Room</h3><p class="text-muted-foreground dark:text-slate-300 mb-4 flex-grow">Spacious 35m² room with a king-size bed and stunning city views.</p><span class="text-2xl font-bold text-secondary font-serif mb-4">$299<span class="text-sm font-sans text-muted-foreground">/night</span></span><button class="w-full bg-primary hover:bg-primary/90 text-primary-foreground py-3 rounded-lg font-semibold transition-transform transform hover:scale-105">Book Now</button></div></div>
                    <div class="bg-card dark:bg-slate-800 rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-shadow duration-300" data-aos="fade-up" data-aos-delay="100"><img src="https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=80" alt="Executive Suite" class="w-full h-64 object-cover"><div class="p-6 flex flex-col"><h3 class="text-2xl font-bold font-serif mb-3 text-foreground dark:text-white">Executive Suite</h3><p class="text-muted-foreground dark:text-slate-300 mb-4 flex-grow">Luxurious 65m² suite with a separate living area and private balcony.</p><span class="text-2xl font-bold text-secondary font-serif mb-4">$499<span class="text-sm font-sans text-muted-foreground">/night</span></span><button class="w-full bg-primary hover:bg-primary/90 text-primary-foreground py-3 rounded-lg font-semibold transition-transform transform hover:scale-105">Book Now</button></div></div>
                    <div class="bg-card dark:bg-slate-800 rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-shadow duration-300" data-aos="fade-up" data-aos-delay="200"><img src="https://images.unsplash.com/photo-1595526114035-0d45ed16433d?auto=format&fit=crop&w=800&q=80" alt="Presidential Suite" class="w-full h-64 object-cover"><div class="p-6 flex flex-col"><h3 class="text-2xl font-bold font-serif mb-3 text-foreground dark:text-white">Presidential Suite</h3><p class="text-muted-foreground dark:text-slate-300 mb-4 flex-grow">Ultimate luxury in our 120m² penthouse with panoramic views.</p><span class="text-2xl font-bold text-secondary font-serif mb-4">$999<span class="text-sm font-sans text-muted-foreground">/night</span></span><button class="w-full bg-primary hover:bg-primary/90 text-primary-foreground py-3 rounded-lg font-semibold transition-transform transform hover:scale-105">Book Now</button></div></div>
                </div>
            </div>
        </section>

        <!-- Gallery Section -->
        <section id="gallery" class="py-24 bg-card dark:bg-slate-800">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
                <h2 class="text-4xl font-bold text-center mb-16 font-serif text-foreground dark:text-white" data-aos="fade-up">Explore Our <span class="text-secondary">Moments</span></h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="https://images.unsplash.com/photo-1542314831-068cd1dbb5eb?auto=format&fit=crop&w=1200&q=80" class="gallery-item block relative overflow-hidden rounded-lg shadow-lg cursor-pointer" data-aos="zoom-in-up"><img src="https://images.unsplash.com/photo-1542314831-068cd1dbb5eb?auto=format&fit=crop&w=800&q=80" alt="Hotel Exterior" class="w-full h-full object-cover transform hover:scale-110 transition-transform duration-300"><div class="overlay absolute inset-0 bg-black/50 flex items-center justify-center text-white p-2 text-center"><i data-feather="zoom-in" class="w-8 h-8"></i></div></a>
                    <a href="https://images.unsplash.com/photo-1564501049412-61c2a3083791?auto=format&fit=crop&w=1200&q=80" class="gallery-item block relative overflow-hidden rounded-lg shadow-lg cursor-pointer" data-aos="zoom-in-up" data-aos-delay="100"><img src="https://images.unsplash.com/photo-1564501049412-61c2a3083791?auto=format&fit=crop&w=800&q=80" alt="Hotel Poolside" class="w-full h-full object-cover transform hover:scale-110 transition-transform duration-300"><div class="overlay absolute inset-0 bg-black/50 flex items-center justify-center text-white p-2 text-center"><i data-feather="zoom-in" class="w-8 h-8"></i></div></a>
                    <a href="https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=1200&q=80" class="gallery-item block relative overflow-hidden rounded-lg shadow-lg cursor-pointer" data-aos="zoom-in-up" data-aos-delay="200"><img src="https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=800&q=80" alt="Luxury Bathroom" class="w-full h-full object-cover transform hover:scale-110 transition-transform duration-300"><div class="overlay absolute inset-0 bg-black/50 flex items-center justify-center text-white p-2 text-center"><i data-feather="zoom-in" class="w-8 h-8"></i></div></a>
                    <a href="https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=1200&q=80" class="gallery-item block relative overflow-hidden rounded-lg shadow-lg cursor-pointer" data-aos="zoom-in-up" data-aos-delay="300"><img src="https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=800&q=80" alt="Bedroom View" class="w-full h-full object-cover transform hover:scale-110 transition-transform duration-300"><div class="overlay absolute inset-0 bg-black/50 flex items-center justify-center text-white p-2 text-center"><i data-feather="zoom-in" class="w-8 h-8"></i></div></a>
                </div>
            </div>
        </section>

        <!-- Special Offers Section -->
        <section id="offers" class="py-24 bg-background dark:bg-slate-900">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
                <h2 class="text-4xl font-bold text-center mb-16 font-serif text-foreground dark:text-white" data-aos="fade-up">Special <span class="text-secondary">Offers</span> For You</h2>
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="flex flex-col sm:flex-row items-center bg-card dark:bg-slate-800 border border-border dark:border-slate-700 rounded-xl overflow-hidden shadow-lg" data-aos="fade-right"><img src="https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=800&q=80" alt="Honeymoon Package" class="w-full sm:w-1/3 h-64 sm:h-full object-cover"><div class="p-6"><h3 class="text-2xl font-bold font-serif mb-3 text-foreground dark:text-white">Honeymoon Package</h3><p class="text-muted-foreground dark:text-slate-300 mb-4">Celebrate your love with our romantic package, including a suite upgrade and champagne.</p><a href="#" class="font-semibold text-secondary hover:underline">Learn More &rarr;</a></div></div>
                    <div class="flex flex-col sm:flex-row items-center bg-card dark:bg-slate-800 border border-border dark:border-slate-700 rounded-xl overflow-hidden shadow-lg" data-aos="fade-left"><img src="https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=800&q=80" alt="Business Traveler" class="w-full sm:w-1/3 h-64 sm:h-full object-cover"><div class="p-6"><h3 class="text-2xl font-bold font-serif mb-3 text-foreground dark:text-white">Business Traveler</h3><p class="text-muted-foreground dark:text-slate-300 mb-4">Stay productive with high-speed Wi-Fi and meeting room access. Stay 3 nights, get 1 free.</p><a href="#" class="font-semibold text-secondary hover:underline">Learn More &rarr;</a></div></div>
                </div>
            </div>
        </section>
        
        <!-- Testimonials -->
        <section class="py-24 bg-muted dark:bg-slate-800/50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
                <h2 class="text-4xl font-bold text-center mb-16 font-serif text-foreground dark:text-white" data-aos="fade-up">What Our <span class="text-secondary">Guests</span> Say</h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="bg-card dark:bg-slate-800 border border-border dark:border-slate-700 rounded-xl p-8 text-center" data-aos="fade-up"><div class="text-secondary mb-4">★★★★★</div><p class="text-foreground dark:text-slate-300 mb-6 italic">"Absolutely stunning hotel with impeccable service. The attention to detail is remarkable."</p><p class="text-foreground dark:text-white font-semibold">- Sarah Johnson</p></div>
                    <div class="bg-card dark:bg-slate-800 border border-border dark:border-slate-700 rounded-xl p-8 text-center" data-aos="fade-up" data-aos-delay="100"><div class="text-secondary mb-4">★★★★★</div><p class="text-foreground dark:text-slate-300 mb-6 italic">"The most luxurious stay I've ever experienced. Every moment was perfect."</p><p class="text-foreground dark:text-white font-semibold">- Michael Chen</p></div>
                    <div class="bg-card dark:bg-slate-800 border border-border dark:border-slate-700 rounded-xl p-8 text-center" data-aos="fade-up" data-aos-delay="200"><div class="text-secondary mb-4">★★★★★</div><p class="text-foreground dark:text-slate-300 mb-6 italic">"Outstanding hospitality and breathtaking views. Will definitely return!"</p><p class="text-foreground dark:text-white font-semibold">- Emma Rodriguez</p></div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="py-24 bg-primary dark:bg-primary/90 text-primary-foreground">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-4xl" data-aos="zoom-in">
                <h2 class="text-4xl font-bold font-serif mb-6">Ready for Your <span class="text-secondary">Luxury</span> Experience?</h2>
                <p class="text-xl mb-8 text-primary-foreground/80">Book your stay today and discover what makes Grand Luxe extraordinary.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#booking-form" class="bg-secondary hover:bg-secondary/90 text-secondary-foreground px-8 py-3 rounded-full text-lg font-semibold transition-all transform hover:scale-105 shadow-lg">Book Your Stay</a>
                    <a href="#rooms" class="border border-white text-white hover:bg-white hover:text-primary px-8 py-3 rounded-full text-lg font-semibold transition-all">View Rooms & Rates</a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer id="contact" class="bg-background dark:bg-slate-900 border-t border-border dark:border-slate-800 py-16">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
            <div class="grid md:grid-cols-3 gap-8 text-center md:text-left">
                <div data-aos="fade-up"><h3 class="text-2xl font-bold mb-4 font-serif text-foreground dark:text-white">Grand Luxe</h3><p class="text-muted-foreground dark:text-slate-400">123 Luxury Avenue<br>Downtown District, City 12345</p></div>
                <div data-aos="fade-up" data-aos-delay="100"><h4 class="text-lg font-semibold mb-4 font-serif text-foreground dark:text-white">Contact</h4><p class="text-muted-foreground dark:text-slate-400">Phone: +1 (555) 123-4567<br>Email: info@grandluxe.com</p></div>
                <div data-aos="fade-up" data-aos-delay="200"><h4 class="text-lg font-semibold mb-4 font-serif text-foreground dark:text-white">Follow Us</h4><div class="flex space-x-4 justify-center md:justify-start"><a href="#" class="text-muted-foreground dark:text-slate-400 hover:text-secondary transition-colors"><i data-feather="facebook"></i></a><a href="#" class="text-muted-foreground dark:text-slate-400 hover:text-secondary transition-colors"><i data-feather="instagram"></i></a><a href="#" class="text-muted-foreground dark:text-slate-400 hover:text-secondary transition-colors"><i data-feather="twitter"></i></a></div></div>
            </div>
            <div class="border-t border-border dark:border-slate-700 mt-12 pt-8 text-center"><p class="text-muted-foreground dark:text-slate-400">© 2024 Grand Luxe. All rights reserved.</p></div>
        </div>
    </footer>

    <!-- Gallery Modal -->
    <div id="gallery-modal" class="hidden fixed inset-0 bg-black/80 z-[100] flex items-center justify-center p-4 transition-opacity duration-300">
        <button id="modal-close" class="absolute top-4 right-4 text-white hover:text-secondary transition-colors"><i data-feather="x" class="w-10 h-10"></i></button>
        <img id="modal-image" src="" alt="Gallery Image" class="max-w-full max-h-full rounded-lg shadow-2xl">
    </div>

    <!-- AOS (Animate On Scroll) Library JS -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({ duration: 800, once: true, offset: 50 });

        // Feather Icons initialization
        feather.replace();

        // Header scroll effect
        const header = document.getElementById('main-header');
        window.addEventListener('scroll', () => {
            header.classList.toggle('header-scrolled', window.scrollY > 50);
            header.classList.toggle('border-b', window.scrollY <= 50);
            header.classList.toggle('border-transparent', window.scrollY <= 50);
        });

        // Dark Mode Toggle Logic
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.documentElement;
        const applyTheme = (theme) => html.classList.toggle('dark', theme === 'dark');
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        applyTheme(savedTheme);
        themeToggle.addEventListener('click', () => {
            const newTheme = html.classList.contains('dark') ? 'light' : 'dark';
            localStorage.setItem('theme', newTheme);
            applyTheme(newTheme);
        });

        // Mobile Menu Logic
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenuButton.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
        document.querySelectorAll('.mobile-link').forEach(link => {
            link.addEventListener('click', () => mobileMenu.classList.add('hidden'));
        });

        // Set min date for date pickers to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('checkin').setAttribute('min', today);
        document.getElementById('checkout').setAttribute('min', today);

        // Animated Number Counter Logic
        const counters = document.querySelectorAll('.number-counter');
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = +counter.getAttribute('data-target');
                    let current = 0;
                    const increment = target / 100;
                    const updateCounter = () => {
                        current += increment;
                        if (current < target) {
                            counter.innerText = Math.ceil(current).toLocaleString();
                            requestAnimationFrame(updateCounter);
                        } else {
                            counter.innerText = target.toLocaleString();
                        }
                    };
                    updateCounter();
                    obs.unobserve(counter);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(counter => observer.observe(counter));

        // Gallery Modal Logic
        const galleryModal = document.getElementById('gallery-modal');
        const modalImage = document.getElementById('modal-image');
        const modalClose = document.getElementById('modal-close');
        document.querySelectorAll('.gallery-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                modalImage.src = item.href;
                galleryModal.classList.remove('hidden');
            });
        });
        const closeModal = () => galleryModal.classList.add('hidden');
        modalClose.addEventListener('click', closeModal);
        galleryModal.addEventListener('click', (e) => {
            if (e.target === galleryModal) closeModal();
        });
    </script>
</body>
</html>
