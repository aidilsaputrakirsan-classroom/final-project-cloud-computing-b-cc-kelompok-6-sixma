@extends('layouts.admin-master')

@section('content')

{{-- 🛑 PROTEKSI DATA NULL DARI CONTROLLER 🛑 --}}
@if(empty($image) || !isset($image['id']))
    <div class="p-6 bg-red-100 border border-red-400 rounded-xl dark:bg-red-900/50 dark:border-red-600">
        <h1 class="text-xl text-red-700 dark:text-red-300">❌ Gagal Mengambil Data Postingan.</h1>
        <p class="text-red-600 dark:text-red-400">Pastikan koneksi Supabase Anda aktif, atau Post ID {{ $image['id'] ?? 'N/A' }} tidak valid. Ini bukan error routing.</p>
        <a href="{{ route('admin.dashboard') }}" class="mt-3 inline-block text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">← Kembali ke Dashboard</a>
    </div>
    
    {{-- HENTIKAN RENDERING JIKA DATA NULL --}}
    @else 

{{-- HEADER DETAIL --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Detail Postingan #{{ $image['id'] }}</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm">Review dan Aksi Administratif</p>
    </div>
    
    {{-- Tombol Kembali --}}
    <a href="{{ route('admin.dashboard') }}" 
       class="px-4 py-2 rounded-lg bg-indigo-500 text-white text-sm hover:bg-indigo-600 transition">
        ← Kembali ke Dashboard
    </a>
</div>

{{-- STATUS LAPORAN (DUMMY CHECK) --}}
@php
    $isReported = ($image['id'] == 2451 || $image['id'] == 2450) ? true : false;
@endphp

@if($isReported)
<div class="mb-6 p-4 rounded-xl border border-red-500/30 bg-red-500/10 dark:border-red-400/30">
    <div class="flex items-center">
        <iconify-icon icon="solar:danger-circle-bold-duotone" class="text-2xl text-red-600 dark:text-red-400 mr-3"></iconify-icon>
        <div>
            <p class="font-semibold text-red-700 dark:text-red-400">Postingan ini telah dilaporkan!</p>
            <p class="text-red-600 text-sm dark:text-red-500">Mohon segera lakukan peninjauan dan putuskan aksi yang tepat.</p>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    {{-- KOLOM KIRI (GAMBAR & INFO ADMIN) --}}
    <div class="lg:col-span-2 space-y-6">
        
        {{-- CARD GAMBAR --}}
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-md dark:bg-[#020617] dark:border-[#1f2937]">
            <h2 class="text-xl font-semibold dark:text-white mb-4">Postingan: {{ $image['title'] ?? 'N/A' }}</h2>
            
            <div class="w-full h-96 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center mb-4 dark:bg-[#1f2937]">
                <img src="{{ $image['image_url'] ?? '/images/placeholder.jpg' }}" 
                     alt="{{ $image['title'] ?? 'Gambar' }}" 
                     class="max-h-full object-contain">
            </div>
            
            <p class="text-gray-600 dark:text-gray-400 mt-4">{{ $image['description'] ?? 'Tidak ada deskripsi.' }}</p>
        </div>
        
        {{-- CARD KOMENTAR --}}
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-md dark:bg-[#020617] dark:border-[#1f2937]">
            <h2 class="text-xl font-semibold dark:text-white mb-4">Komentar ({{ count($image['comments'] ?? []) }})</h2>

            @forelse($image['comments'] ?? [] as $comment)
            <div class="border-b border-gray-100 py-3 dark:border-[#111827]">
                <div class="flex justify-between items-center mb-1">
                    <p class="font-medium text-gray-800 dark:text-gray-300">
                        {{ $comment['users']['name'] ?? 'Pengguna Tidak Dikenal' }}
                        <span class="text-xs text-gray-400 ml-2">({{ $comment['created_at'] ? date('d M Y', strtotime($comment['created_at'])) : 'Tanggal N/A' }})</span>
                    </p>
                    {{-- Aksi Admin pada Komentar --}}
                    <button class="text-xs text-red-500 hover:text-red-700">Hapus Komentar</button>
                </div>
                <p class="text-gray-600 text-sm dark:text-gray-400">{{ $comment['content'] }}</p>
            </div>
            @empty
            <p class="text-gray-500 text-sm">Belum ada komentar pada postingan ini.</p>
            @endforelse
        </div>
    </div>
    
    {{-- KOLOM KANAN (DATA ADMIN & AKSI) --}}
    <div class="lg:col-span-1 space-y-6">
        
        {{-- CARD METADATA & USER --}}
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-md dark:bg-[#020617] dark:border-[#1f2937]">
            <h2 class="text-lg font-semibold dark:text-white mb-4">Metadata</h2>
            <div class="space-y-3 text-sm">
                <p class="flex justify-between border-b pb-1 dark:border-[#111827]"><span class="font-medium text-gray-500">ID Post:</span> <span class="dark:text-white">{{ $image['id'] ?? 'N/A' }}</span></p>
                <p class="flex justify-between border-b pb-1 dark:border-[#111827]"><span class="font-medium text-gray-500">Diunggah:</span> <span class="dark:text-white">{{ $image['created_at'] ? date('d M Y', strtotime($image['created_at'])) : 'N/A' }}</span></p>
                <p class="flex justify-between border-b pb-1 dark:border-[#111827]"><span class="font-medium text-gray-500">Kategori:</span> <span class="dark:text-white">{{ $image['category_name'] ?? 'N/A' }}</span></p>
                <p class="flex justify-between border-b pb-1 dark:border-[#111827]"><span class="font-medium text-gray-500">Total Likes:</span> <span class="dark:text-white">{{ $image['like_count'] ?? 0 }}</span></p>
                <p class="flex justify-between"><span class="font-medium text-gray-500">Uploader:</span> <span class="dark:text-white">{{ $image['users']['name'] ?? 'N/A' }}</span></p>
            </div>
            
            <button class="mt-4 w-full px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 text-sm hover:bg-gray-200 dark:bg-[#111827] dark:text-gray-300 dark:hover:bg-[#1f2937] transition">
                Lihat Profile User
            </button>
        </div>
        
        {{-- CARD AKSI ADMIN --}}
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-md dark:bg-[#020617] dark:border-[#1f2937]">
            <h2 class="text-lg font-semibold dark:text-white mb-4">Aksi Cepat</h2>
            
            <div class="space-y-3">
                <button class="w-full px-4 py-2 rounded-lg bg-red-600 text-white text-sm hover:bg-red-700 transition">
                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="mr-1"></iconify-icon> Hapus Postingan Permanen
                </button>
                <button class="w-full px-4 py-2 rounded-lg bg-orange-500 text-white text-sm hover:bg-orange-600 transition">
                    <iconify-icon icon="solar:user-block-bold-duotone" class="mr-1"></iconify-icon> Blokir User
                </button>
                <button class="w-full px-4 py-2 rounded-lg bg-yellow-500 text-white text-sm hover:bg-yellow-600 transition">
                    <iconify-icon icon="solar:eye-scan-bold-duotone" class="mr-1"></iconify-icon> Tinjau Laporan (Jika Ada)
                </button>
            </div>
        </div>
    </div>
</div>

@endif {{-- Tutup @if empty($image) --}}

@endsection