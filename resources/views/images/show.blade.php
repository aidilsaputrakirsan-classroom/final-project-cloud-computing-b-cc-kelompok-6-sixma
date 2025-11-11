<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $image['title'] }} - Detail Karya</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* ======== STYLING CSS ANDA YANG SUDAH ADA ======== */
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

        /* PERUBAHAN: Menghilangkan padding agar konten di dalam card lebih fleksibel */
        .image-content {
            padding: 0 20px;
            text-align: left;
        }

        .image-card h2 {
            color: #F6C74D;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: center; /* Pastikan judul tetap di tengah */
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
            text-align: center;
        }

        .desc {
            color: #ddd;
            font-size: 1rem;
            margin-top: 10px;
            margin-bottom: 25px;
            text-align: justify; /* Deskripsi lebih baik rata kiri-kanan */
        }
        
        /* ======== STYLING KOMENTAR BARU ======== */
        .comment-area {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(246, 199, 77, 0.2);
            text-align: left;
        }

        .comment-item {
            background-color: rgba(30, 30, 30, 0.9);
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 10px;
            position: relative;
        }

        .comment-item small {
            color: #888;
        }

        .comment-input-form textarea {
            background: rgba(40, 40, 40, 0.9);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            resize: none;
        }

        .comment-input-form textarea:focus {
            background-color: rgba(50, 50, 50, 0.9);
            border-color: #F6C74D;
            box-shadow: none;
        }
        
        /* Tombol yang sudah ada */
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
        /* ======== AKHIR STYLING CSS ANDA ======== */
    </style>
</head>
<body>

<div class="container py-5">
    <div class="image-card">
        <h2>{{ $image['title'] }}</h2>

        {{-- Gambar dari Supabase --}}
        <img src="{{ $image['image_url'] ?? 'https://via.placeholder.com/700x500?text=Gambar+Hilang' }}" 
             alt="{{ $image['title'] }}"
             onerror="this.src='https://via.placeholder.com/500x350?text=Image+Not+Found'">
        
        <div class="image-content">
            {{-- Informasi detail --}}
            <div class="info text-center">
                {{-- Asumsi Anda sudah mengolah created_at dengan Carbon di Controller atau View --}}
                📅 Diunggah pada {{ \Carbon\Carbon::parse($image['created_at'] ?? now())->format('d M Y, H:i') }}
            </div>

            {{-- Deskripsi Gambar --}}
            <p class="desc">
                {{ $image['description'] ?? 'Tidak ada deskripsi tersedia.' }}
            </p>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                {{-- Penulis Gambar (Jika ada) --}}
                <div class="fw-bold text-light">
                    Oleh: {{ $image['user_name'] ?? 'Pengguna Artrium' }} 
                </div>
                
                {{-- Tombol aksi --}}
                <div class="d-flex gap-3">
                    {{-- Tombol Edit HANYA jika pengguna adalah pemilik (Logika otorisasi di sini harus diterapkan) --}}
                    {{-- Asumsi $image['user_id'] tersedia dan sama dengan Auth::id() --}}
                    @auth
                        @if (Auth::id() == $image['user_id'])
                            <a href="{{ route('images.edit', $image['id']) }}" class="btn btn-warning btn-sm">Edit Karya</a>
                        @endif
                    @endauth

                    <a href="{{ route('gallery.index') }}" class="btn btn-secondary btn-sm">Kembali ke Galeri</a>
                </div>
            </div>
            
            <div class="comment-area">
                <h4 class="text-light mb-3">Komentar (0)</h4>
                
                {{-- NOTIFIKASI SUKSES/GAGAL --}}
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                {{-- FORM KOMENTAR BARU --}}
                @auth
                    <form action="{{ route('comments.store', $image['id']) }}" method="POST" class="comment-input-form mb-4">
                        @csrf
                        <div class="mb-2">
                            <textarea name="content" class="form-control" rows="2" placeholder="Tulis komentar Anda..." required></textarea>
                            @error('content')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Kirim Komentar</button>
                    </form>
                @else
                    <p class="text-center text-gray-500">Silakan <a href="{{ route('login') }}" class="text-warning">Login</a> untuk meninggalkan komentar.</p>
                @endauth

                {{-- DAFTAR KOMENTAR (Placeholder) --}}
                <div id="comments-list">
                    {{-- Di sini tempat loop data komentar akan ditempatkan --}}
                    {{-- @foreach ($comments as $comment) --}}
                        <div class="comment-item">
                            <div class="fw-bold text-warning">Nama Pengguna</div>
                            <p class="mb-1">Ini adalah contoh komentar pertama.</p>
                            <small class="text-muted">5 menit yang lalu</small>
                            {{-- Tombol Delete (Hanya jika Auth::id() == $comment['user_id']) --}}
                        </div>
                    {{-- @endforeach --}}
                </div>
            </div>
        </div>
        
    </div>
</div>

</body>
</html>