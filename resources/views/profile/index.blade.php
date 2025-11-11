<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->name }} - Profil | Artrium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .profile-header {
            background-color: #1a1a1a;
            border-bottom: 1px solid #333;
        }
        .card-image {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-black text-white min-h-screen pt-20"> 
    
    {{-- 🔹 Navigation Bar --}}
    <nav class="fixed top-0 w-full bg-black bg-opacity-90 z-50 px-6 py-4 border-b border-gray-800">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="text-2xl font-bold">Artrium</div>
            
            <div class="hidden md:flex space-x-8">
                <a href="{{ route('home') }}" class="hover:text-yellow-400 transition">Home</a>
                <a href="{{ route('gallery.index') }}" class="hover:text-yellow-400 transition">Explore</a>
                <a href="{{ route('profile.show') }}" class="hover:text-yellow-400 transition">Profile</a>

            </div>
            
            <div class="flex space-x-3 items-center">
                @auth
                    <a href="{{ route('images.create') }}" class="px-4 py-2 bg-yellow-400 text-black rounded-full hover:bg-yellow-500 transition font-semibold text-sm">
                       Unggah Karya
                    </a>
                    <span class="text-sm text-gray-400">Halo, {{ $user->name ?? 'User' }} </span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-6 py-2 border border-white rounded-full hover:bg-white hover:text-black transition text-sm">
                            Logout
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>
    
    {{-- 🔹 Profile Header --}}
    <header class="profile-header py-10">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-5xl font-bold mb-2 text-yellow-400">{{ $user->name }}</h1>
            <p class="text-gray-400 text-lg mb-4">{{ $user->email }}</p>
            <p class="text-gray-500">{{ count($images) }} karya telah diunggah</p>
        </div>
    </header>
    
    {{-- 🔹 Main Content --}}
    <main class="py-12 px-6">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-8 text-white">Karya Saya</h2>
            
            @if (count($images) > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach ($images as $image)
                        <div class="relative group overflow-hidden rounded-lg bg-gray-900 border border-gray-800 hover:border-yellow-400 transition">
                            <img src="{{ $image['image_url'] ?? '#' }}" 
                                 alt="{{ $image['title'] }}" 
                                 class="w-full card-image transform group-hover:scale-[1.05] transition duration-500">

                            <div class="p-4">
                                <h3 class="font-semibold text-lg text-white">{{ $image['title'] }}</h3>
                                <p class="text-sm text-gray-400">
                                    {{ $image['category']['name'] ?? 'Tanpa Kategori' }} | 
                                    {{ \Carbon\Carbon::parse($image['created_at'])->translatedFormat('d M Y') }}
                                </p>
                                
                                <div class="mt-3 flex justify-between items-center">
                                    <a href="{{ route('images.edit', $image['id']) }}" 
                                       class="text-xs px-3 py-1 bg-yellow-400 text-black rounded hover:bg-yellow-500 font-semibold">
                                        Edit
                                    </a>
                                    
                                    <form action="{{ route('images.destroy', $image['id']) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus karya ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 font-semibold">
                                            Delete
                                        </button>
                                    </form>

                                    <a href="{{ route('images.show', $image['id']) }}" 
                                       class="text-xs px-3 py-1 bg-gray-700 text-white rounded hover:bg-gray-600 font-semibold">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 text-xl py-10">
                    Anda belum mengunggah karya apapun. Yuk, unggah karya pertama Anda!
                </p>
            @endif
        </div>
    </main>
    
    {{-- 🔹 Footer --}}
    <footer class="text-center py-6 border-t border-gray-800 text-gray-500 text-sm">
        © 2025 Artrium Project - Kelompok 6
    </footer>
</body>
</html>
