<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $image['title'] }} - Detail Karya</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    {{-- Memastikan Carbon tersedia untuk formatting tanggal --}}
    @php use Carbon\Carbon; @endphp
    
    <script src="https://cdn.tailwindcss.com"></script> 
    
    <style>
        /* ======== STYLING CSS ANDA ======== */
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

        .image-content {
            padding: 0 20px;
            text-align: left;
        }

        .image-card h2 {
            color: #F6C74D;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: center;
        }

        .image-card img {
            max-width: 100%;
            max-height: 85vh; 
            width: auto;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(246, 199, 77, 0.25);
            margin-bottom: 25px;
            transition: all 0.3s ease;
            object-fit: cover;
            display: block;
            margin-left: auto;
            margin-right: auto;
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
            text-align: justify;
        }
        
        /* ======== STYLING KOMENTAR & BUTTONS ======== */
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
        }

        .comment-input-form textarea {
            background: rgba(40, 40, 40, 0.9);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            resize: none;
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

        .btn-secondary {
            background-color: rgba(255, 255, 255, 0.1);
            border: none;
            color: #f8f9fa;
            font-weight: 600;
            border-radius: 12px;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="image-card">
        <h2>{{ $image['title'] ?? 'Judul Tidak Ada' }}</h2>

        {{-- Gambar dari Supabase --}}
        <img src="{{ $image['image_url'] ?? 'https://via.placeholder.com/700x500?text=Gambar+Hilang' }}" 
             alt="{{ $image['title'] ?? 'Gambar' }}"
             onerror="this.src='https://via.placeholder.com/500x350?text=Image+Not+Found'">
        
        <div class="image-content">
            
            {{-- Bagian Metadata Gambar (Kategori & Tanggal) --}}
            <div class="info text-center mt-3">
                @if(!empty($image['category_id']))
                    <span class="badge bg-warning text-dark me-2">🏷️ Kategori: {{ $image['category_name'] ?? $image['category_id'] }}</span> 
                @endif
                {{-- Tanggal --}}
                <span class="text-white date">
                     • 📅 Diunggah pada {{ Carbon::parse($image['created_at'] ?? now())->format('d M Y, H:i') }}
                </span>
            </div>

            {{-- Deskripsi Gambar --}}
            <p class="desc">
                {{ $image['description'] ?? 'Tidak ada deskripsi tersedia.' }}
            </p>
            
            {{-- Penulis & Tombol Aksi --}}
            <div class="d-flex justify-content-between align-items-center mb-4 border-top pt-3">
                
                <div class="fw-bold text-light">
                    Oleh: <span class="text-warning">{{ $image['user_name'] ?? 'Pengguna Artrium' }}</span>
                </div>
                
                {{-- Tombol aksi --}}
                <div class="d-flex gap-3">
                    {{-- @auth
                        @if (Auth::id() == $image['user_id'])
                            <a href="{{ route('images.edit', $image['id']) }}" class="btn btn-warning btn-sm">Edit Karya</a>
                        @endif
                    @endauth --}}

                    <a href="{{ route('gallery.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
            </div>
            
            <div class="comment-area">
                
                {{-- PERBAIKAN: COUNTER KOMENTAR DINAMIS --}}
                <h4 class="text-light mb-3">Komentar ({{ count($comments ?? []) }})</h4>
                
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

                {{-- PERBAIKAN: DAFTAR KOMENTAR DINAMIS --}}
                <div id="comments-list">
                    @forelse ($comments ?? [] as $comment)
                        <div class="comment-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                {{-- Menggunakan data user yang di-JOIN dari ImageController --}}
                                <strong class="text-warning">{{ $comment['user']['name'] ?? 'Pengguna Artrium' }}</strong>
                                {{-- Memformat tanggal agar lebih mudah dibaca --}}
                                <small class="text-muted">{{ Carbon::parse($comment['created_at'])->diffForHumans() }}</small>
                            </div>
                            {{-- Isi Komentar --}}
                            <p class="text-light mb-0">{{ $comment['content'] }}</p> 
                        </div>
                    @empty
                        {{-- Teks ini akan muncul jika array $comments kosong --}}
                        <p class="text-center text-gray-500">Belum ada komentar untuk karya ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>