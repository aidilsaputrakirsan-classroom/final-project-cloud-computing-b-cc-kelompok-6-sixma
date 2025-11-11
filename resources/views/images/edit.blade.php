@extends('layouts.app') {{-- Menggunakan Layout Induk --}}

@section('title', 'Edit Karya: ' . ($image['title'] ?? 'Gambar'))

@push('styles')
    {{-- 🛑 PENTING: TAMBAHKAN PEMUATAN TAILWIND & BOOTSTRAP (untuk kelas seperti d-flex) --}}
    {{-- Asumsi Anda menggunakan Bootstrap/sejenisnya untuk kelas d-flex, form-label, etc. --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    {{-- Tailwind seharusnya sudah ada di layouts.app, tapi jika tidak, tambahkan: --}}
    <script src="https://cdn.tailwindcss.com"></script>


    {{-- Menambahkan CSS kustom Anda --}}
    <style>
        /* CSS Umum dan Utility Classes (Tailwind harus dimuat agar ini bekerja) */
        .text-light { color: #f8f9fa !important; } /* Memperbaiki kelas dari Bootstrap */
        .form-label { display: block; margin-bottom: 0.5rem; } /* Memperbaiki kelas dari Bootstrap */
        .form-control:focus { border-color: #F6C74D; box-shadow: 0 0 0 0.25rem rgba(246, 199, 77, 0.25); }
        .btn-secondary:hover { background-color: rgba(255, 255, 255, 0.2); }

        /* Kustom Edit Card Anda */
        .edit-card {
            max-width: 650px;
            margin: 0px auto; 
            background: rgba(20, 20, 20, 0.85);
            border: 1px solid rgba(246, 199, 77, 0.25);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 0 30px rgba(246, 199, 77, 0.15);
            backdrop-filter: blur(10px);
        }

        h2 {
            text-align: center;
            font-weight: 700;
            color: #F6C74D;
            margin-bottom: 30px;
        }

        label {
            color: #F6C74D;
            font-weight: 600;
        }
        
        .current-image img {
            max-width: 100%;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(246, 199, 77, 0.25);
            transition: all 0.3s ease;
        }
        .img-preview { max-height: 150px; }
        
        /* Styling Input dan Button */
        .form-control { 
            background-color: #121212; 
            color: #fff; 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            border-radius: 10px; 
            padding: 10px 14px; 
            width: 100%; /* Memastikan input memenuhi lebar */
        }
        .btn-primary { 
            background-color: #F6C74D; 
            border: none; 
            color: #0A0A0A; 
            font-weight: 700; 
            border-radius: 12px; 
            padding: 10px 20px;
        }
        .btn-secondary { 
            background-color: rgba(255, 255, 255, 0.1); 
            border: none; 
            color: #f8f9fa; 
            font-weight: 600; 
            border-radius: 12px; 
            padding: 10px 20px;
        }

    </style>
@endpush

@section('content')
<div class="container py-12 px-4 md:px-6"> {{-- Tambahkan kelas Tailwind untuk padding dan centering --}}
    <div class="edit-card">
        <h2 class="mb-2">Edit Karya</h2>
        <h5 class="text-center text-light mb-4">{{ $image['title'] ?? 'Tanpa Judul' }}</h5>

        {{-- Form Edit Menggunakan Metode PATCH --}}
        <form action="{{ route('images.update', $image['id']) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH') 

            {{-- Judul Gambar --}}
            <div class="mb-3">
                <label for="title" class="form-label">Judul</label>
                <input type="text" name="title" id="title" class="form-control" 
                        value="{{ old('title', $image['title'] ?? '') }}" required>
            </div>
            
            {{-- Deskripsi Gambar --}}
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $image['description'] ?? '') }}</textarea>
            </div>

            {{-- Kategori (Dropdown) --}}
            <div class="mb-4">
                <label for="category_id" class="form-label">Kategori</label>
                <select name="category_id" id="category_id" class="form-control" required>
                    <option value="" disabled>Pilih Kategori</option>
                    {{-- Loop Kategori dari Controller --}}
                    @foreach ($categories as $category)
                        <option value="{{ $category['id'] }}" 
                                {{ ($image['category_id'] ?? '') == $category['id'] ? 'selected' : '' }}>
                            {{ $category['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            {{-- Gambar Saat Ini & Input File Baru --}}
            <div class="mb-3">
                <label for="image" class="form-label">Ganti File Gambar (Opsional)</label>
                <input type="file" name="image" id="image" class="form-control" accept="image/*" onchange="previewImage(event)">
                <small class="text-gray-400 mt-1 block">Biarkan kosong jika tidak ingin mengganti gambar.</small>

                {{-- Preview gambar baru --}}
                <div class="image-preview mb-3 mt-3" id="previewContainer" style="display: none;">
                    <img id="previewImage" alt="Preview Gambar Baru" class="img-preview">
                </div>

                {{-- Gambar saat ini --}}
                @if(isset($image['image_url']))
                    <div class="current-image mt-3">
                        <p class="text-warning mb-2 text-yellow-500">Gambar saat ini:</p>
                        <img src="{{ $image['image_url'] }}" alt="Current image" class="img-fluid img-preview"
                                    onerror="this.src='https://via.placeholder.com/200x150?text=Image+Not+Found'">
                    </div>
                @endif
            </div>
            
            {{-- Tombol Batal mengarah ke Halaman Profile --}}
            {{-- Menggunakan kelas Tailwind (flex, justify-center, space-x-4) menggantikan d-flex justify-content-center gap-2 --}}
            <div class="flex justify-center space-x-4 mt-8"> 
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('profile.show') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage(event) {
        // ... Logika JavaScript Preview Anda ...
        const file = event.target.files[0];
        const previewContainer = document.getElementById('previewContainer');
        const previewImage = document.getElementById('previewImage');
        const currentImage = document.querySelector('.current-image');

        if (file) {
            previewImage.src = URL.createObjectURL(file);
            previewContainer.style.display = 'block';
            if (currentImage) {
                 currentImage.style.display = 'none'; // Sembunyikan gambar lama
            }
        } else {
            previewContainer.style.display = 'none';
            if (currentImage) {
                 currentImage.style.display = 'block';
            }
        }
    }
</script>
@endsection