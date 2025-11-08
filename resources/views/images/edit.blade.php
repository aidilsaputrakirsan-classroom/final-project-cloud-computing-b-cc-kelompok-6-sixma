<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Gambar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container py-4">
    <h2 class="mb-4 text-center">Edit Gambar</h2>

    <form action="{{ route('images.update', $image->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="title" class="form-label">Judul Gambar</label>
            <input type="text" name="title" id="title" value="{{ $image->title }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Gambar Sekarang</label><br>
            <img src="{{ asset('storage/' . $image->path) }}" width="200" class="mb-3">
        </div>

        <div class="mb-3">
            <label for="image" class="form-label">Ganti Gambar (opsional)</label>
            <input type="file" name="image" id="image" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('gallery') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>

</body>
</html>
