<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artrium - Temukan Kedamaian di Setiap Bidikan Alam</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white">
    <!-- Navigation -->
    <nav class="fixed top-0 w-full bg-black bg-opacity-90 z-50 px-6 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="text-2xl font-bold">Artrium</div>
            
            <div class="hidden md:flex space-x-8">
                <a href="#" class="hover:text-yellow-400 transition">Home</a>
                <a href="#" class="hover:text-yellow-400 transition">Explore</a>
            </div>
            
        <div class="flex space-x-3">
    @auth
        {{-- Jika user sudah login --}}
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" 
                class="px-6 py-2 border border-white rounded-full hover:bg-white hover:text-black transition">
                Logout
            </button>
        </form>
    @else
        {{-- Jika user belum login --}}
        <a href="{{ route('register') }}" 
           class="px-6 py-2 border border-white rounded-full hover:bg-white hover:text-black transition">
            Sign up
        </a>
        <a href="{{ route('login') }}" 
           class="px-6 py-2 bg-yellow-400 text-black rounded-full hover:bg-yellow-500 transition">
            Login
        </a>
    @endauth
</div>

    </nav>

    <!-- Hero Section -->
    <section class="relative h-screen flex items-center justify-center" style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1920') center/cover;">
        <div class="text-center px-4 max-w-4xl">
            <h1 class="text-5xl md:text-6xl font-bold mb-4">
                Temukan Kedamaian di Setiap<br>
                <span class="text-yellow-400">Bidikan Alam.</span>
            </h1>
            <p class="text-lg md:text-xl mb-8 text-gray-200">
                Dari gunung hingga lautan, setiap foto menyimpan kisah tentang<br>
                tentang dunia yang menakjubkan.
            </p>
            
            <!-- Search Bar -->
            <div class="max-w-2xl mx-auto relative">
                <input 
                    type="text" 
                    placeholder="Cari Inspirasimu" 
                    class="w-full px-6 py-4 rounded-full bg-gray-800 bg-opacity-80 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400"
                >
                <button class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-yellow-400 text-black p-3 rounded-full hover:bg-yellow-500 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="py-20 px-6 bg-black">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    Cerita Alam dari<br>
                    <span class="text-yellow-400">Lensa Mereka</span>
                </h2>
                <p class="text-gray-400 text-lg">
                    Setiap foto adalah jendela unik tentang dunia – jelajahi karya odhey,<br>
                    dan ketenangan dari para fotografer di seluruh penjuru bumi.
                </p>
            </div>

            <!-- Masonry Grid -->
            <div class="columns-1 md:columns-2 lg:columns-3 xl:columns-4 gap-4 space-y-4">
                <!-- Image Card 1 -->
                <div class="break-inside-avoid">
                    <div class="relative group overflow-hidden rounded-lg cursor-pointer">
                        <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400" alt="Diamnya Danau Tua" class="w-full h-auto transform group-hover:scale-110 transition duration-500">
                        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black to-transparent">
                            <h3 class="font-semibold text-lg">Diamnya Danau Tua</h3>
                            <p class="text-sm text-gray-300">John Richerd</p>
                        </div>
                    </div>
                </div>

                <!-- Image Card 2 -->
                <div class="break-inside-avoid">
                    <div class="relative group overflow-hidden rounded-lg cursor-pointer">
                        <img src="https://images.unsplash.com/photo-1494500764479-0c8f2919a3d8?w=400" alt="Sernja di Balik Awan" class="w-full h-auto transform group-hover:scale-110 transition duration-500">
                        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black to-transparent">
                            <h3 class="font-semibold text-lg">Sernja di Balik Awan</h3>
                            <p class="text-sm text-gray-300">Anderson Diana</p>
                        </div>
                    </div>
                </div>

                <!-- Image Card 3 -->
                <div class="break-inside-avoid">
                    <div class="relative group overflow-hidden rounded-lg cursor-pointer">
                        <img src="https://images.unsplash.com/photo-1419242902214-272b3f66ee7a?w=400" alt="Birunya Langit Utara" class="w-full h-auto transform group-hover:scale-110 transition duration-500">
                        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black to-transparent">
                            <h3 class="font-semibold text-lg">Birunya Langit Utara</h3>
                            <p class="text-sm text-gray-300">Davi Markcus</p>
                        </div>
                    </div>
                </div>

                <!-- Image Card 4 -->
                <div class="break-inside-avoid">
                    <div class="relative group overflow-hidden rounded-lg cursor-pointer">
                        <img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=400" alt="Bisikan Hutan Pagi" class="w-full h-auto transform group-hover:scale-110 transition duration-500">
                        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black to-transparent">
                            <h3 class="font-semibold text-lg">Bisikan Hutan Pagi</h3>
                            <p class="text-sm text-gray-300">Mikaela Kamellia</p>
                        </div>
                    </div>
                </div>

                <!-- Image Card 5 -->
                <div class="break-inside-avoid">
                    <div class="relative group overflow-hidden rounded-lg cursor-pointer">
                        <img src="https://images.unsplash.com/photo-1490750967868-88aa4486c946?w=400" alt="Harmoni di Padang Rumput" class="w-full h-auto transform group-hover:scale-110 transition duration-500">
                        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black to-transparent">
                            <h3 class="font-semibold text-lg">Harmoni di Padang Rumput</h3>
                            <p class="text-sm text-gray-300">Beach Martinez</p>
                        </div>
                    </div>
                </div>

                <!-- Image Card 6 -->
                <div class="break-inside-avoid">
                    <div class="relative group overflow-hidden rounded-lg cursor-pointer">
                        <img src="https://images.unsplash.com/photo-1506929562872-bb421503ef21?w=400" alt="Hening di Ujung Tebing" class="w-full h-auto transform group-hover:scale-110 transition duration-500">
                        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black to-transparent">
                            <h3 class="font-semibold text-lg">Hening di Ujung Tebing</h3>
                            <p class="text-sm text-gray-300">Topu Alficki</p>
                        </div>
                    </div>
                </div>

                <!-- Image Card 7 -->
                <div class="break-inside-avoid">
                    <div class="relative group overflow-hidden rounded-lg cursor-pointer">
                        <img src="https://images.unsplash.com/photo-1433838552652-f9a46b332c40?w=400" alt="Rintik di Pagi Tenang" class="w-full h-auto transform group-hover:scale-110 transition duration-500">
                        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black to-transparent">
                            <h3 class="font-semibold text-lg">Rintik di Pagi Tenang</h3>
                            <p class="text-sm text-gray-300">Dafi Aullana</p>
                        </div>
                    </div>
                </div>

                <!-- Image Card 8 -->
                <div class="break-inside-avoid">
                    <div class="relative group overflow-hidden rounded-lg cursor-pointer">
                        <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400" alt="Ketika Laut Menyapa" class="w-full h-auto transform group-hover:scale-110 transition duration-500">
                        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black to-transparent">
                            <h3 class="font-semibold text-lg">Ketika Laut Menyapa</h3>
                            <p class="text-sm text-gray-300">Satang Nujrilla</p>
                        </div>
                    </div>
                </div>

                <!-- Image Card 9 -->
                <div class="break-inside-avoid">
                    <div class="relative group overflow-hidden rounded-lg cursor-pointer">
                        <img src="https://images.unsplash.com/photo-1518495973542-4542c06a5843?w=400" alt="Dedaunan yang Menari" class="w-full h-auto transform group-hover:scale-110 transition duration-500">
                        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black to-transparent">
                            <h3 class="font-semibold text-lg">Dedaunan yang Menari</h3>
                            <p class="text-sm text-gray-300">Azka Parameswara</p>
                        </div>
                    </div>
                </div>

                <!-- Image Card 10 -->
                <div class="break-inside-avoid">
                    <div class="relative group overflow-hidden rounded-lg cursor-pointer">
                        <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400" alt="Suara Ombak di Senja" class="w-full h-auto transform group-hover:scale-110 transition duration-500">
                        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black to-transparent">
                            <h3 class="font-semibold text-lg">Suara Ombak di Senja</h3>
                            <p class="text-sm text-gray-300">Niar Azhira</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>