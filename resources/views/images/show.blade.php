<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $image['title'] }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container py-5 text-center">
    <h2>{{ $image['title'] }}</h2>

    {{-- Gunakan URL yang sudah dibentuk di controller --}}
    <img src="{{ $image['image_url'] }}" 
         class="img-fluid rounded shadow my-4" 
         alt="{{ $image['title'] }}">

    <div>
        <a href="{{ route('images.edit', $image['id']) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('gallery.index') }}" class="btn btn-secondary">Kembali ke Galeri</a>
    </div>
</div>

</body>
</html>
