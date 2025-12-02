<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artrium - Galeri Foto Alam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- KRITIS: Tambahkan Font Awesome untuk Ikon Hati --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    {{-- FIX KRITIS: TAMBAHKAN META TAG CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        .like-button {
            display: flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            transition: color 0.2s;
        }
        .text-red-500 {
            color: #ef4444; /* Tailwind red-500 equivalent */
        }
        .text-gray-400 {
            color: #9ca3af; /* Tailwind gray-400 equivalent */
        }
        /* Style untuk notifikasi error/success custom */
        .custom-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: opacity 0.5s, transform 0.5s;
        }
        .custom-alert.hidden {
            opacity: 0;
            transform: translateY(-20px);
        }
        .alert-success-custom {
            background-color: #10B981; /* Green-500 */
            color: white;
        }
        .alert-error-custom {
            background-color: #DC2626; /* Red-600 */
            color: white;
        }
    </style>
</head>
<body class="bg-black text-white min-h-screen pt-20"> 
    
    <div id="custom-notification" class="custom-alert hidden"></div>

    <nav class="fixed top-0 w-full bg-black bg-opacity-90 z-50 px-6 py-4 border-b border-gray-800">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="text-2xl font-bold">Artrium</div>
            
            <div class="hidden md:flex space-x-8">
                <a href="{{ route('home') }}" class="hover:text-yellow-400 transition">Home</a>
                <a href="{{ route('gallery.index') }}" class="text-yellow-400 font-semibold transition">Explore</a>
                
                {{-- TOMBOL PROFILE --}}
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

            {{-- NOTIFIKASI SUKSES/ERROR (Diambil dari session) --}}
            @if (session('success'))
                <div class="bg-green-700 text-white p-4 rounded-lg shadow-lg mb-4">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-700 text-white p-4 rounded-lg shadow-lg mb-4">{{ session('error') }}</div>
            @endif
            
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
                        {{-- NOTE: Anda perlu memastikan 'categories' dikirimkan dari ImageController::index juga --}}
                        @if (!empty($categories)) 
                            @foreach ($categories as $category)
                                <option value="{{ $category['id'] ?? '' }}" {{ request('category') == ($category['id'] ?? '') ? 'selected' : '' }}>
                                    {{ $category['name'] ?? 'N/A' }}
                                </option>
                            @endforeach
                        @else
                            {{-- Placeholder jika categories tidak terambil --}}
                            <option value="1">Gunung</option>
                            <option value="2">Laut</option>
                            <option value="3">Hutan</option>
                        @endif
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
                
                {{-- LOOP DATA DARI DATABASE --}}
                @forelse ($images as $image)
                    <div class="relative group overflow-hidden rounded-lg bg-gray-900 border border-gray-800 hover:border-yellow-400 transition">
                        <img 
                            src="{{ $image['image_url'] ?? asset('images/default.jpg') }}" 
                            alt="{{ $image['title'] ?? 'Tanpa Judul' }}" 
                            class="w-full h-48 object-cover transform group-hover:scale-[1.05] transition duration-500">
                        
                        <div class="p-4">
                            <h3 class="font-semibold text-lg text-white">
                                {{ $image['title'] ?? 'Tanpa Judul' }}
                            </h3>
                            <p class="text-sm text-gray-400">
                                ID Kategori: {{ $image['category_id'] ?? '-' }} |
                                {{ \Carbon\Carbon::parse($image['created_at'])->translatedFormat('d M Y') }}
                            </p>
                            <div class="mt-3 flex justify-between items-center">
                                {{-- Link View Dinamis --}}
                                <a href="{{ route('images.show', $image['id']) }}" 
                                   class="text-xs px-3 py-1 bg-gray-700 text-white rounded hover:bg-gray-600 font-semibold">
                                    View
                                </a>
                                
                                {{-- Tombol Suka (Integrated with JS) --}}
                                <button type="button" 
                                    class="like-button" 
                                    onclick="handleLike('{{ $image['id'] }}', this)">
                                    
                                    @php
                                        // Tentukan kelas ikon berdasarkan status 'is_liked'
                                        $iconClass = ($image['is_liked'] ?? false) 
                                            ? 'fa-solid text-red-500' 
                                            : 'fa-regular text-gray-400 hover:text-red-500';
                                    @endphp
                                    
                                    <i class="fa-heart {{ $iconClass }}"></i>
                                    <span class="like-count text-sm text-gray-400">{{ $image['like_count'] ?? 0 }}</span>
                                </button>
                                
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center text-gray-400 py-10">
                        Tidak ada karya ditemukan.
                    </div>
                @endforelse
            </div>
            
        </div>
    </main>
    
    <footer class="text-center py-6 border-t border-gray-800 text-gray-500 text-sm">
        © 2025 Artrium Project - Kelompok 6
    </footer>
    
    {{-- KRITIS: JAVASCRIPT UNTUK FUNGSI LIKES --}}
    <script>
        // Gunakan Base URL agar lebih stabil, daripada mencoba memproses route() di JS
        const BASE_URL = '{{ url('/') }}';
        
        // Fungsi untuk menampilkan notifikasi kustom
        function showNotification(message, type = 'error') {
            const container = document.getElementById('custom-notification');
            container.textContent = message;
            container.classList.remove('alert-success-custom', 'alert-error-custom', 'hidden');
            
            if (type === 'success') {
                container.classList.add('alert-success-custom');
            } else {
                container.classList.add('alert-error-custom');
            }

            // Sembunyikan setelah 4 detik
            setTimeout(() => {
                container.classList.add('hidden');
            }, 4000);
        }

        function handleLike(imageId, buttonElement) {
            // Cek apakah user sudah login
            if (!{{ Auth::check() ? 'true' : 'false' }}) {
                showNotification('Anda harus Login untuk menyukai karya ini.', 'error');
                return;
            }

            const countElement = buttonElement.querySelector('.like-count');
            const iconElement = buttonElement.querySelector('i');
            
            // Nonaktifkan tombol sementara
            buttonElement.disabled = true;

            // Ambil CSRF Token dari meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // GUNAKAN URL BASE DAN ID GAMBAR
            const url = `${BASE_URL}/images/${imageId}/like`;

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => {
                // Check if response is 404/403/401 and handle gracefully
                if (!response.ok) {
                    // Coba baca JSON error dari server
                    return response.json().then(data => {
                        throw new Error(data.error || `Server error (Status: ${response.status})`);
                    }).catch(() => {
                        // Jika bukan JSON (misalnya HTML 404/419), throw error generik
                        throw new Error(`Gagal memanggil rute (Status: ${response.status}).`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update ikon dan hitungan berdasarkan response dari LikeController::toggle
                    if (data.action === 'liked') {
                        iconElement.classList.remove('fa-regular', 'text-gray-400', 'hover:text-red-500');
                        iconElement.classList.add('fa-solid', 'text-red-500');
                        showNotification('Karya disukai!', 'success');
                    } else {
                        iconElement.classList.remove('fa-solid', 'text-red-500');
                        iconElement.classList.add('fa-regular', 'text-gray-400', 'hover:text-red-500');
                        showNotification('Batal menyukai karya.', 'success');
                    }
                    countElement.textContent = data.like_count;
                } else {
                    showNotification('Gagal memproses like: ' + (data.error || 'Terjadi kesalahan server.'), 'error');
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                showNotification('Error: ' + error.message, 'error');
            })
            .finally(() => {
                // Aktifkan kembali tombol
                buttonElement.disabled = false;
            });
        }
    </script>
</body>
</html>