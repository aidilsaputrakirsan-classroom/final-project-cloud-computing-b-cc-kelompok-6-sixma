<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Gambar Baru - Artrium</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* ======== LATAR & FONT (Kode CSS Anda di sini) ======== */
        /* ... (Semua CSS Anda di sini) ... */
        body {
            background: radial-gradient(circle at top, #1a1a1a, #0A0A0A);
            color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }

        .upload-card {
            width: 100%;
            max-width: 600px;
            background: rgba(20, 20, 20, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(246, 199, 77, 0.2);
            border-radius: 25px;
            box-shadow: 0 0 40px rgba(246, 199, 77, 0.15);
            padding: 40px;
            transition: all 0.4s ease;
        }
        .upload-card:hover {
            box-shadow: 0 0 50px rgba(246, 199, 77, 0.3);
            transform: translateY(-4px);
        }

        h2 {
            color: #F6C74D;
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
            letter-spacing: 1px;
        }

        label {
            color: #F6C74D;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .form-control {
            background: rgba(30, 30, 30, 0.95);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 10px 14px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        /* Tambahan styling untuk Select/Dropdown */
        select.form-control {
            appearance: none; /* Hilangkan default arrow di beberapa browser */
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Cpath fill='%23F6C74D' d='M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 0.8em 0.8em;
            padding-right: 2.5rem;
        }


        .form-control::placeholder {
            color: #c9a94a;
            opacity: 0.8;
        }

        .form-control:focus {
            border-color: #F6C74D;
            box-shadow: 0 0 12px rgba(246, 199, 77, 0.45);
            background-color: rgba(40, 40, 40, 0.95);
            color: #fff;
        }

        .btn-success {
            background-color: #F6C74D;
            border: none;
            color: #0A0A0A;
            font-weight: 700;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 0 10px rgba(246, 199, 77, 0.4);
        }
        .btn-success:hover {
            background-color: #FFD85C;
            box-shadow: 0 0 25px rgba(246, 199, 77, 0.6);
        }

        .btn-secondary {
            background-color: rgba(255, 255, 255, 0.1);
            border: none;
            color: #f8f9fa;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .image-preview {
            display: none;
            margin-top: 15px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(246, 199, 77, 0.3);
            animation: fadeIn 0.4s ease-in-out;
        }

        .image-preview img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 12px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.98); }
            to { opacity: 1; transform: scale(1); }
        }

        @media (max-width: 576px) {
            .upload-card {
                padding: 25px;
                border-radius: 15px;
            }
        }
    </style>
</head>
<body>

<div class="upload-card">
    <h2>Upload Gambar Baru</h2>

    <form action="{{ route('images.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="title" class="form-label">Judul Gambar</label>
            <input type="text" name="title" id="title" class="form-control" placeholder="Masukkan judul gambar" required>
        </div>
        
        {{-- FIELD BARU: DESKRIPSI --}}
        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi Gambar (Opsional)</label>
            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Jelaskan kisah dibalik bidikan ini..."></textarea>
        </div>

        <div class="mb-3">
            <label for="image" class="form-label">File Gambar</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*" required onchange="previewImage(event)">
            <div class="image-preview" id="previewContainer">
                <img id="previewImage" alt="Preview Gambar">
            </div>
        </div>

        {{-- FIELD BARU: KATEGORI SEBAGAI DROPDOWN --}}
        <div class="mb-4">
            <label for="category_id" class="form-label">Kategori</label>
            <select name="category_id" id="category_id" class="form-control" required>
                <option value="" disabled selected>Pilih Kategori</option>
                
                {{-- DI SINI TEMPAT LOOP DATA KATEGORI DARI DATABASE --}}
                {{-- @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach --}}
                
                {{-- Contoh Statis --}}
                <option value="1">Nature / Alam</option>
                <option value="2">Urban / Kota</option>
                <option value="3">Portrait / Manusia</option>
                
            </select>
        </div>
        
        {{-- Tambahan: Input User ID tersembunyi (Disarankan menggunakan Auth::user()->id di Controller, bukan di View) --}}

        <div class="text-center">
            <button type="submit" class="btn btn-success px-4 me-2">Upload</button>
            <a href="{{ route('gallery.index') }}" class="btn btn-secondary px-4">Kembali</a>
        </div>
    </form>
</div>

<script>
    function previewImage(event) {
        const file = event.target.files[0];
        const previewContainer = document.getElementById('previewContainer');
        const previewImage = document.getElementById('previewImage');

        if (file) {
            previewImage.src = URL.createObjectURL(file);
            previewContainer.style.display = 'block';
        } else {
            previewContainer.style.display = 'none';
        }
    }
</script>

</body>
</html>