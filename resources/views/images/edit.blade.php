@extends('layouts.app') {{-- Menggunakan Layout Induk --}}

@section('title', 'Edit Karya: ' . ($image['title'] ?? 'Gambar'))

@push('styles')
    {{-- Menambahkan CSS kustom Anda --}}
    <style>
        .edit-card {
            max-width: 650px;
            margin: 0px auto; /* Margin diatur ulang karena berada di dalam @section('content') */
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
        
        /* ... Tambahkan sisa styling input dan button Anda di sini ... */
        .form-control { background-color: #121212; color: #fff; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; padding: 10px 14px; }
        .btn-primary { background-color: #F6C74D; border: none; color: #0A0A0A; font-weight: 700; border-radius: 12px; }
        .btn-secondary { background-color: rgba(255, 255, 255, 0.1); border: none; color: #f8f9fa; font-weight: 600; border-radius: 12px; }

    </style>
@endpush

@section('content')
<div class="container py-3">
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
                <small class="text-muted">Biarkan kosong jika tidak ingin mengganti gambar.</small>

                {{-- Preview gambar baru --}}
                <div class="image-preview mb-3" id="previewContainer" style="display: none;">
                    <img id="previewImage" alt="Preview Gambar Baru">
                </div>

                {{-- Gambar saat ini --}}
                @if(isset($image['image_url']))
                    <div class="current-image mt-3">
                        <p class="text-warning mb-2">Gambar saat ini:</p>
                        <img src="{{ $image['image_url'] }}" alt="Current image" class="img-fluid img-preview"
                              onerror="this.src='https://via.placeholder.com/200x150?text=Image+Not+Found'">
                    </div>
                @endif
            </div>
            
            {{-- Tombol Batal mengarah ke Halaman Profile --}}
            <div class="d-flex justify-content-center gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                <a href="{{ route('profile.show') }}" class="btn btn-secondary px-4">Batal</a>
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

        if (file) {
            previewImage.src = URL.createObjectURL(file);
            previewContainer.style.display = 'block';
            // Sembunyikan gambar lama saat ada preview baru (opsional)
            document.querySelector('.current-image')?.style.display = 'none'; 
        } else {
            previewContainer.style.display = 'none';
            document.querySelector('.current-image')?.style.display = 'block';
        }
    }
</script>
@endsection