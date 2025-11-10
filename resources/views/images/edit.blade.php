<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit {{ $image['title'] ?? 'Image' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ======== WARNA DASAR ======== */
        body {
            background-color: #0A0A0A;
            color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }

        /* ======== CARD UTAMA ======== */
        .edit-card {
            max-width: 650px;
            margin: 60px auto;
            background: rgba(20, 20, 20, 0.85);
            border: 1px solid rgba(246, 199, 77, 0.25);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 0 30px rgba(246, 199, 77, 0.15);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .edit-card:hover {
            box-shadow: 0 0 40px rgba(246, 199, 77, 0.25);
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

        /* ======== INPUT & FILE ======== */
        .form-control {
            background-color: #121212;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 10px 14px;
            transition: all 0.3s ease;
        }

        .form-control::placeholder {
            color: #c9a94a;
            opacity: 0.8;
        }

        .form-control:focus {
            border-color: #F6C74D;
            box-shadow: 0 0 10px rgba(246, 199, 77, 0.4);
            background-color: #181818;
            color: #fff;
        }

        small.text-muted {
            color: #c1c1c1 !important;
        }

        /* ======== BUTTONS ======== */
        .btn-primary {
            background-color: #F6C74D;
            border: none;
            color: #0A0A0A;
            font-weight: 700;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 0 10px rgba(246, 199, 77, 0.3);
        }

        .btn-primary:hover {
            background-color: #FFD85C;
            box-shadow: 0 0 20px rgba(246, 199, 77, 0.6);
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

        /* ======== GAMBAR SAAT INI ======== */
        .current-image img {
            max-width: 100%;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(246, 199, 77, 0.25);
            transition: all 0.3s ease;
        }

        .current-image img:hover {
            transform: scale(1.03);
            box-shadow: 0 0 25px rgba(246, 199, 77, 0.35);
        }

        .image-preview {
            display: none;
            margin-top: 10px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(246, 199, 77, 0.3);
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
    </style>
</head>
<body>

<div class="edit-card">
    <h2>Edit Gambar</h2>

    <form action="{{ route('images.update', $image['id']) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        
        <div class="mb-3">
            <label for="title" class="form-label">Judul</label>
            <input type="text" class="form-control" id="title" name="title" 
                   value="{{ $image['title'] }}" required>
        </div>
        
        <div class="mb-3">
            <label for="category" class="form-label">Kategori</label>
            <input type="text" class="form-control" id="category" name="category" 
                   value="{{ $image['category'] ?? '' }}" 
                   placeholder="Nature, Urban, Portrait, dll">
        </div>
        
        <div class="mb-3">
            <label for="image" class="form-label">Gambar Baru (Opsional)</label>
            <input type="file" class="form-control" id="image" name="image" onchange="previewImage(event)">
            <small class="text-muted">Biarkan kosong jika tidak ingin mengganti gambar</small>

            <!-- Preview gambar baru -->
            <div class="image-preview" id="previewContainer">
                <img id="previewImage" alt="Preview Gambar Baru">
            </div>

            <!-- Gambar saat ini -->
            @if(isset($image['image_path']))
                <div class="current-image mt-3">
                    <p class="text-warning mb-2">Gambar saat ini:</p>
                    <?php
                        $baseStorageUrl = rtrim(env('SUPABASE_URL'), '/') . '/storage/v1/object/public/images/';
                        $currentImageUrl = $baseStorageUrl . $image['image_path'];
                    ?>
                    <img src="{{ $currentImageUrl }}" alt="Current image"
                         onerror="this.src='https://via.placeholder.com/200x150?text=Image+Not+Found'">
                </div>
            @endif
        </div>
        
        <div class="mb-4">
            <label for="location" class="form-label">Lokasi</label>
            <input type="text" class="form-control" id="location" name="location" 
                   value="{{ $image['location'] ?? '' }}" placeholder="Contoh: Balikpapan, Kalimantan Timur">
        </div>
        
        <div class="d-flex justify-content-center gap-2">
            <button type="submit" class="btn btn-primary px-4">Update</button>
            <a href="{{ route('images.show', $image['id']) }}" class="btn btn-secondary px-4">Batal</a>
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
            previewContainer.style.animation = 'fadeIn 0.4s ease-in-out';
        } else {
            previewContainer.style.display = 'none';
        }
    }
</script>

</body>
</html>
