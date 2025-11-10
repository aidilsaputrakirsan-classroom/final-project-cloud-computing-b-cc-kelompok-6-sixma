<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $image['title'] }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #0A0A0A;
            color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }

        .image-card {
            max-width: 750px;
            margin: 60px auto;
            background: rgba(20, 20, 20, 0.9);
            border: 1px solid rgba(246, 199, 77, 0.25);
            border-radius: 20px;
            box-shadow: 0 0 35px rgba(246, 199, 77, 0.15);
            backdrop-filter: blur(10px);
            text-align: center;
            padding: 40px;
        }

        .image-card h2 {
            color: #F6C74D;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .image-card img {
            max-width: 100%;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(246, 199, 77, 0.25);
            margin-bottom: 25px;
            transition: all 0.3s ease;
            object-fit: cover;
        }

        .image-card img:hover {
            transform: scale(1.02);
            box-shadow: 0 0 35px rgba(246, 199, 77, 0.4);
        }

        .info {
            color: #c9a94a;
            font-size: 0.95rem;
            margin-bottom: 10px;
        }

        .desc {
            color: #ddd;
            font-size: 1rem;
            margin-top: 10px;
        }

        .btn-warning {
            background-color: #F6C74D;
            border: none;
            color: #0A0A0A;
            font-weight: 700;
            border-radius: 12px;
            padding: 10px 20px;
            transition: all 0.3s ease;
            box-shadow: 0 0 10px rgba(246, 199, 77, 0.3);
        }

        .btn-warning:hover {
            background-color: #FFD85C;
            box-shadow: 0 0 20px rgba(246, 199, 77, 0.5);
        }

        .btn-secondary {
            background-color: rgba(255, 255, 255, 0.1);
            border: none;
            color: #f8f9fa;
            font-weight: 600;
            border-radius: 12px;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .date {
            color: #aaa;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="image-card">
        <h2>{{ $image['title'] }}</h2>

        {{-- Gambar dari Supabase --}}
        <img src="{{ $image['image_url'] }}" 
             alt="{{ $image['title'] }}"
             onerror="this.src='https://via.placeholder.com/500x350?text=Image+Not+Found'">

        {{-- Informasi detail --}}
        <div class="info">
            @if(!empty($image['category'])) 🏷️ {{ $image['category'] }} @endif
            @if(!empty($image['location']))
                @if(!empty($image['category'])) • @endif 📍 {{ $image['location'] }}
            @endif
        </div>

        <div class="date">
            📅 Diunggah pada {{ \Carbon\Carbon::parse($image['created_at'])->format('d M Y, H:i') }}
        </div>

        {{-- Tombol aksi --}}
        <div class="mt-4 d-flex justify-content-center gap-3">
            <a href="{{ route('images.edit', $image['id']) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('gallery.index') }}" class="btn btn-secondary">Kembali ke Galeri</a>
        </div>
    </div>
</div>

</body>
</html>
