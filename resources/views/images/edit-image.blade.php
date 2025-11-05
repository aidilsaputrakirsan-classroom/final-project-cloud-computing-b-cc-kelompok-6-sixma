@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 max-w-2xl">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-6 md:p-8">
        
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-6 border-b pb-3">
            Perbarui Karya: {{ $image['title'] ?? 'Gambar' }}
        </h1>
        
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('images.update', $image['id']) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH') 
            
            <div class="mb-5">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Judul Karya <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" 
                       value="{{ old('title', $image['title'] ?? '') }}" 
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:text-white" required>
                @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori <span class="text-red-500">*</span></label>
                <select id="category_id" name="category_id" 
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:text-white" required>
                    <option value="">Pilih Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category['id'] }}" 
                                {{ 
                                    (old('category_id') == $category['id']) ? 'selected' : 
                                    ( ($image['category_id'] ?? '') == $category['id'] ? 'selected' : '' ) 
                                }}>
                            {{ $category['name'] }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lokasi Pengambilan Gambar <span class="text-red-500">*</span></label>
                <input type="text" id="location" name="location" 
                       value="{{ old('location', $image['location'] ?? '') }}" 
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:text-white" required>
                @error('location')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-5">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi Karya (Opsional)</label>
                <textarea id="description" name="description" rows="4" 
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:text-white">{{ old('description', $image['description'] ?? '') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gambar Saat Ini:</label>
                <img src="{{ env('SUPABASE_URL') }}/storage/v1/object/public/images/{{ $image['image_path'] ?? '' }}" 
                     alt="Gambar Lama" 
                     class="max-w-xs h-auto rounded-lg shadow-md border border-gray-200">
            </div>

            <div class="mb-6">
                <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ganti Gambar (Opsional)</label>
                <input type="file" id="image" name="image" 
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Biarkan kosong jika tidak ingin mengganti gambar. Maksimal 2MB (sesuaikan dengan validasi).</p>
                @error('image')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('images.show', $image['id']) }}" class="px-5 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 bg-yellow-500 text-gray-900 font-semibold rounded-lg hover:bg-yellow-600 transition duration-200">
                    Simpan Perubahan
                </button>
            </div>
        </form>

    </div>
</div>
@endsection