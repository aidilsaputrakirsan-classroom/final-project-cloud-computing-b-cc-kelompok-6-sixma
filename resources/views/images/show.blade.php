@extends('layouts.app') 

@section('content')

<div class="container mx-auto p-6 max-w-4xl">

    <a href="{{ route('images.index') }}" class="text-blue-500 hover:text-blue-600 mb-6 inline-flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Kembali ke Galeri
    </a>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('success') }}
        </div>
    @endif
    
    @if ($image)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
            
            <div class="p-4">
                <img src="{{ env('SUPABASE_URL') }}/storage/v1/object/public/images/{{ $image['image_path'] }}" 
                     alt="{{ $image['title'] }}" 
                     class="w-full h-auto object-cover rounded-lg shadow-lg">
            </div>

            <div class="p-6 md:p-8">
                <h1 class="text-4xl font-extrabold text-gray-900 dark:text-gray-100 mb-2">{{ $image['title'] }}</h1>
                
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-6 border-b pb-4">
                    <p class="mb-1">
                        **Kategori:** <span class="font-medium text-yellow-500">{{ $image['category'] ?? 'General' }}</span>
                    </p>
                    <p class="mb-1">
                        **Lokasi:** 📍 {{ $image['location'] ?? 'Tidak Diketahui' }}
                    </p>
                    {{-- Asumsi ada kolom 'created_at' dari Supabase --}}
                    <p>
                        **Diunggah pada:** {{ \Carbon\Carbon::parse($image['created_at'])->format('d M Y') }} 
                    </p>
                </div>

                <div class="mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">Deskripsi Karya</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                        {{ $image['description'] ?? 'Tidak ada deskripsi yang disediakan untuk karya ini.' }}
                    </p>
                </div>

                @auth
                    {{-- Otorisasi: Tampilkan aksi hanya jika user adalah pemilik gambar --}}
                    @if (Auth::id() == $image['user_id'])
                    <div class="border-t pt-4 flex space-x-4">
                        <a href="{{ route('images.edit', $image['id']) }}" 
                           class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition duration-200">
                            Edit Karya
                        </a>
                        
                        <form action="{{ route('images.destroy', $image['id']) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="px-5 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition duration-200" 
                                    onclick="return confirm('ANDA YAKIN INGIN MENGHAPUS KARYA INI? Aksi ini tidak bisa dibatalkan.')">
                                Hapus Karya
                            </button>
                        </form>
                    </div>
                    @endif
                    
                    {{-- Logic Admin Role juga bisa ditambahkan di sini --}}
                @endauth

                {{-- <div class="mt-8 border-t pt-6">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Komentar</h3>
                    </div> --}}

            </div>
        </div>
    @else
        <div class="text-center mt-10 p-10 bg-white rounded-lg shadow-lg">
            <h2 class="text-2xl text-red-500 font-bold">Karya Tidak Ditemukan (404)</h2>
            <p class="text-gray-500 mt-2">Gambar yang Anda cari tidak ada atau telah dihapus.</p>
        </div>
    @endif
</div>

@endsection