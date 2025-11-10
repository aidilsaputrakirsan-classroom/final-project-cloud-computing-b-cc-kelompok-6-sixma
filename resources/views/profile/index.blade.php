@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-black text-white py-12">
    <div class="max-w-5xl mx-auto flex flex-col items-center text-center space-y-6">

        {{-- Foto Profil --}}
        <img src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
             alt="Foto Profil"
             class="w-32 h-32 rounded-full border-4 border-yellow-400 object-cover">

        {{-- Nama & Username --}}
        <div>
            <h1 class="text-3xl font-bold text-yellow-400">{{ $user->name }}</h1>
            <p class="text-gray-400 text-sm">@{{ $user->username ?? $user->email }}</p>
        </div>

        {{-- Tombol Unggah --}}
        <a href="{{ route('images.create') }}"
           class="bg-yellow-500 hover:bg-yellow-600 text-black px-5 py-2 rounded-full text-sm transition">
           + Unggah Karya
        </a>

        {{-- Pembatas --}}
        <div class="w-full border-t border-gray-700 my-8"></div>

        {{-- Daftar Gambar --}}
        <div class="w-full">
            <h2 class="text-left text-xl font-semibold mb-4 text-yellow-400">Karya Kamu</h2>

            @if(count($images) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @foreach($images as $image)
                <div class="bg-neutral-900 p-4 rounded-2xl shadow-lg text-white transition hover:scale-[1.02] duration-200">
                    <img src="{{ $image['image_url'] ?? '/img/default.jpg' }}" 
                         alt="{{ $image['title'] }}" 
                         class="rounded-lg mb-3 object-cover w-full h-48">
                    <h3 class="text-lg font-semibold">{{ $image['title'] }}</h3>
                    <p class="text-xs text-gray-400 mt-1">
                        📅 {{ \Carbon\Carbon::parse($image['created_at'])->translatedFormat('d F Y') }}
                    </p>
                    <div class="mt-3 flex justify-center space-x-3">
                        <a href="{{ route('images.show', $image['id']) }}" 
                           class="bg-gray-700 hover:bg-gray-800 text-white px-3 py-1 rounded-md text-sm">View</a>
                        <a href="{{ route('images.edit', $image['id']) }}" 
                           class="bg-yellow-500 hover:bg-yellow-600 text-black px-3 py-1 rounded-md text-sm">Edit</a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
                <p class="text-gray-500 mt-8">Belum ada karya yang kamu unggah.</p>
            @endif
        </div>
    </div>
</div>
@endsection
