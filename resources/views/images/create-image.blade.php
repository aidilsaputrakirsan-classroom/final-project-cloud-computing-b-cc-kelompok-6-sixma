@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 max-w-lg">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-6 md:p-8">
        
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-6 border-b pb-3">Unggah Karya Baru</h1>
        
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('images.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-5">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Judul Karya</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" class="w-full px-4 py-2 border rounded-lg" required>
                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div class="mb-5">
                <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                <select id="category_id" name="category_id" class="w-full px-4 py-2 border rounded-lg" required>
                    <option value="">Pilih Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category['id'] }}" {{ old('category_id') == $category['id'] ? 'selected' : '' }}>
                            {{ $category['name'] }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-5">
                <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lokasi Pengambilan</label>
                <input type="text" id="location" name="location" value="{{ old('location') }}" class="w-full px-4 py-2 border rounded-lg" required>
                @error('location') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-5">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi Karya</label>
                <textarea id="description" name="description" rows="3" class="w-full px-4 py-2 border rounded-lg">{{ old('description') }}</textarea>
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">File Gambar</label>
                <input type="file" id="image" name="image" class="w-full" required>
                @error('image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full py-3 bg-yellow-500 text-gray-900 font-semibold rounded-lg hover:bg-yellow-600 transition duration-200">
                Unggah Sekarang
            </button>
        </form>

    </div>
</div>
@endsection