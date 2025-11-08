@extends('layouts.app') 

@section('content')

<div class="container mx-auto p-6">
    
    <div class="mb-8 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100">Galeri Foto Alam</h1>
        
        @auth
            <a href="{{ route('images.create') }}" 
               class="px-4 py-2 bg-yellow-500 text-gray-900 font-semibold rounded-lg hover:bg-yellow-600 transition duration-200">
                + Unggah Karya Baru
            </a>
        @endauth
        
        {{-- Form Search akan ditaruh di sini oleh Kirana --}}
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            {{ session('error') }}
        </div>
    @endif


    @if (empty($images))
        <p class="text-center text-gray-500 mt-10">Belum ada karya yang diunggah. Jadilah yang pertama!</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            
            @foreach ($images as $image)
                {{-- 🔴 PERBAIKAN Wajib: Cek apakah item adalah array sebelum mengakses key --}}
                @if (is_array($image) && isset($image['id']))
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden transition transform hover:scale-[1.02]">
                    
                    <a href="{{ route('images.show', $image['id']) }}">
                        <img src="{{ env('SUPABASE_URL') }}/storage/v1/object/public/images/{{ $image['image_path'] }}" 
                             alt="{{ $image['title'] }}" 
                             class="w-full h-auto object-cover"
                             style="max-height: 400px;">
                    </a>

                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1 truncate">{{ $image['title'] }}</h3>
                        
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            📍 {{ $image['location'] ?? 'Lokasi Tidak Diketahui' }} | 
                            #{{ $image['category'] ?? 'General' }}
                        </p>
                        
                        @auth
                            @if (Auth::id() == $image['user_id'])
                            <div class="mt-3 flex space-x-2">
                                <a href="{{ route('images.edit', $image['id']) }}" class="text-blue-500 hover:text-blue-600 text-sm">Edit</a>
                                
                                <form action="{{ route('images.destroy', $image['id']) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-600 text-sm" onclick="return confirm('Yakin ingin menghapus karya ini?')">Hapus</button>
                                </form>
                            </div>
                            @endif
                        @endauth
                    </div>
                </div>
                @endif {{-- Tutup pengecekan array --}}
            @endforeach
        </div>
    @endif
</div>

@endsection