<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit {{ $image['title'] ?? 'Image' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <h2 class="mb-4 text-center">Edit Gambar</h2>

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
            <input type="file" class="form-control" id="image" name="image">
            <small class="text-muted">Biarkan kosong jika tidak ingin mengganti gambar</small>
            
            <!-- Tampilkan gambar saat ini -->
            @if(isset($image['image_path']))
                <div class="mt-2">
                    <p>Gambar saat ini:</p>
                    <?php
                    $baseStorageUrl = rtrim(env('SUPABASE_URL'), '/') . '/storage/v1/object/public/images/';
                    $currentImageUrl = $baseStorageUrl . $image['image_path'];
                    ?>
                    <img src="{{ $currentImageUrl }}" alt="Current image" style="max-width: 200px;" 
                         onerror="this.src='https://via.placeholder.com/200x150?text=Image+Not+Found'">
                </div>
            @endif
        </div>
        
        <div class="mb-3">
            <label for="location" class="form-label">Lokasi</label>
            <input type="text" class="form-control" id="location" name="location" 
                   value="{{ $image['location'] ?? '' }}">
        </div>
        
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('images.show', $image['id']) }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

</body>
</html>