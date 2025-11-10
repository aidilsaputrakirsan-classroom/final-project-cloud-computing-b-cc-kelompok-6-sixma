<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artrium - Galeri Foto Alam</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen pt-20"> 
    
    <nav class="fixed top-0 w-full bg-black bg-opacity-90 z-50 px-6 py-4 border-b border-gray-800">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="text-2xl font-bold">Artrium</div>
            
            <div class="hidden md:flex space-x-8">
                <a href="{{ route('home') }}" class="hover:text-yellow-400 transition">Home</a>
                <a href="{{ route('gallery.index') }}" class="text-yellow-400 font-semibold transition">Explore</a>
                
                {{-- TOMBOL PROFILE BARU --}}
                @auth
                    <a href="{{ route('profile.show') }}" class="hover:text-yellow-400 transition">Profile</a>
                @endauth
            </div>
            
            <div class="flex space-x-3 items-center">
                @auth
                    {{-- TOMBOL UNGGAH KARYA --}}
                    <a href="{{ route('images.create') }}" 
                       class="px-4 py-2 bg-yellow-400 text-black rounded-full hover:bg-yellow-500 transition font-semibold text-sm">
                       Unggah Karya
                    </a>
                    
                    {{-- Teks Halo, [Nama User] --}}
                    <span class="text-sm text-gray-400">
                        Halo, {{ Auth::user()->name ?? 'User' }} 
                    </span>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" 
                            class="px-6 py-2 border border-white rounded-full hover:bg-white hover:text-black transition text-sm">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" 
                       class="px-6 py-2 bg-yellow-400 text-black rounded-full hover:bg-yellow-500 transition text-sm">
                        Login
                    </a>
                @endauth
            </div>
        </div>
    </nav>
    
    <main class="py-12 px-6">
        <div class="max-w-7xl mx-auto">
            
            <header class="text-center mb-10">
                <h1 class="text-5xl font-bold mb-2">
                    Jelajahi <span class="text-yellow-400">Karya Alam</span>
                </h1>
                <p class="text-gray-400 text-lg">
                    Temukan inspirasi dari ribuan bidikan alam dari seluruh penjuru dunia.
                </p>
            </header>

            <div class="bg-gray-900 p-6 rounded-xl shadow-lg mb-12">
                <form action="{{ route('gallery.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-center">
                    
                    <div class="relative flex-grow w-full md:w-auto">
                        <input 
                            type="text" 
                            name="search"
                            placeholder="Cari Judul atau Deskripsi Foto..." 
                            value="{{ request('search') }}"
                            class="w-full px-5 py-3 rounded-full bg-black text-white placeholder-gray-500 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-yellow-400"
                        >
                        <svg class="absolute right-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <select name="category" class="w-full md:w-60 px-5 py-3 rounded-full bg-black text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        <option value="">Semua Kategori</option>
                        {{-- LOOP KATEGORI DARI CONTROLLER --}}
                        {{-- @foreach ($categories as $category)
                            <option value="{{ $category['id'] ?? '' }}" {{ request('category') == ($category['id'] ?? '') ? 'selected' : '' }}>
                                {{ $category['name'] ?? 'N/A' }}
                            </option>
                        @endforeach --}}
                        <option value="1">Gunung</option>
                        <option value="2">Laut</option>
                        <option value="3">Hutan</option>
                    </select>
                    
                    <button type="submit" class="w-full md:w-28 px-5 py-3 bg-yellow-400 text-black rounded-full hover:bg-yellow-500 transition font-semibold">
                        Cari
                    </button>
                    
                    <a href="{{ route('gallery.index') }}" class="w-full md:w-28 text-center text-gray-400 hover:text-white transition">
                        Reset
                    </a>
                </form>
            </div>
            
            <h2 class="text-3xl font-bold text-center mb-8 text-yellow-400">Karya Terbaru</h2>
            
            {{-- Menggunakan grid seragam 4 kolom --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                
                {{-- LOOP DATA DARI DATABASE DI SINI --}}
                {{-- @foreach ($images as $image) ... @endforeach --}}

                <div class="relative group overflow-hidden rounded-lg bg-gray-900 border border-gray-800 hover:border-yellow-400 transition">
                    <img src="https://images.unsplash.com/photo-1543787321-c452e6977799?w=400" alt="Pantai Lamaru" class="w-full h-48 object-cover transform group-hover:scale-[1.05] transition duration-500">
                    <div class="p-4">
                        <h3 class="font-semibold text-lg text-white">Pantai Lamaru</h3>
                        <p class="text-sm text-gray-400">Pantai | 10 Nov 2025</p>
                        <div class="mt-3 flex justify-between items-center">
                            {{-- Tombol View --}}
                            <a href="#" class="text-xs px-3 py-1 bg-gray-700 text-white rounded hover:bg-gray-600 font-semibold">
                                View
                            </a>
                            {{-- Tombol Suka --}}
                            <button type="button" class="text-gray-400 hover:text-red-500 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="relative group overflow-hidden rounded-lg bg-gray-900 border border-gray-800 hover:border-yellow-400 transition">
                    <img src="https://images.unsplash.com/photo-1551000673-a621752b02f1?w=400" alt="Bukit Kebo" class="w-full h-48 object-cover transform group-hover:scale-[1.05] transition duration-500">
                    <div class="p-4">
                        <h3 class="font-semibold text-lg text-white">Bukit Kebo</h3>
                        <p class="text-sm text-gray-400">Bukit | 10 Nov 2025</p>
                        <div class="mt-3 flex justify-between items-center">
                            <a href="#" class="text-xs px-3 py-1 bg-gray-700 text-white rounded hover:bg-gray-600 font-semibold">
                                View
                            </a>
                            <button type="button" class="text-gray-400 hover:text-red-500 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="relative group overflow-hidden rounded-lg bg-gray-900 border border-gray-800 hover:border-yellow-400 transition">
                    <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400" alt="Hutan Pinus" class="w-full h-48 object-cover transform group-hover:scale-[1.05] transition duration-500">
                    <div class="p-4">
                        <h3 class="font-semibold text-lg text-white">Hutan Pinus</h3>
                        <p class="text-sm text-gray-400">Hutan | 09 Nov 2025</p>
                        <div class="mt-3 flex justify-between items-center">
                            <a href="#" class="text-xs px-3 py-1 bg-gray-700 text-white rounded hover:bg-gray-600 font-semibold">
                                View
                            </a>
                            <button type="button" class="text-gray-400 hover:text-red-500 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="relative group overflow-hidden rounded-lg bg-gray-900 border border-gray-800 hover:border-yellow-400 transition">
                    <img src="https://images.unsplash.com/photo-1433838552652-f9a46b332c40?w=400" alt="Pegunungan Salju" class="w-full h-48 object-cover transform group-hover:scale-[1.05] transition duration-500">
                    <div class="p-4">
                        <h3 class="font-semibold text-lg text-white">Pegunungan Salju</h3>
                        <p class="text-sm text-gray-400">Gunung | 08 Nov 2025</p>
                        <div class="mt-3 flex justify-between items-center">
                            <a href="#" class="text-xs px-3 py-1 bg-gray-700 text-white rounded hover:bg-gray-600 font-semibold">
                                View
                            </a>
                            <button type="button" class="text-gray-400 hover:text-red-500 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                
                {{-- Tambahkan 6 Card Placeholder lagi di sini menggunakan struktur yang sama (jika diperlukan untuk mengisi layout) --}}

            </div>
            
        </div>
    </main>
    
    <footer class="text-center py-6 border-t border-gray-800 text-gray-500 text-sm">
        © 2025 Artrium Project - Kelompok 6
    </footer>
</body>
</html>