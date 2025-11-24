<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $image['title'] }} - Detail Karya</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    {{-- Tambahkan Font Awesome untuk Ikon Bendera/Report --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
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
            position: relative; 
            display: flex;
            flex-direction: column;
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
        
        .btn-danger-outline { 
            background: none;
            border: 1px solid #dc3545;
            color: #dc3545;
            font-weight: 600;
            border-radius: 12px;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        .btn-danger-outline:hover {
            background-color: #dc3545;
            color: #fff;
        }
        
        .btn-danger { 
            background-color: #dc3545;
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 12px;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }

        .btn-delete-comment {
            background-color: #dc3545; 
            color: #fff; 
            border: none;
            border-radius: 8px; 
            padding: 4px 10px; 
            font-size: 0.8rem; 
            font-weight: 600;
            transition: background-color 0.2s ease;
            
            position: absolute;
            top: 50%; 
            right: 12px;
            transform: translateY(-50%); 
            line-height: 1; 
        }

        .btn-delete-comment:hover {
            background-color: #c82333; 
            color: #fff;
        }
        
        .action-buttons-group {
            display: flex;
            gap: 10px; 
            justify-content: flex-end; 
            align-items: center; 
            flex-wrap: wrap; 
        }
        .action-buttons-group form {
            margin-bottom: 0; 
        }
        
        /* Modal Styling for Dark Theme */
        .modal-content {
            background-color: rgba(20, 20, 20, 0.95);
            color: #f8f9fa;
            border: 1px solid rgba(246, 199, 77, 0.25);
            border-radius: 15px;
        }
        .modal-header {
            border-bottom: 1px solid rgba(246, 199, 77, 0.1);
        }
        .modal-footer {
            border-top: 1px solid rgba(246, 199, 77, 0.1);
        }
        .form-control-dark {
            background-color: rgba(40, 40, 40, 0.9);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

    </style>
</head>
<body>

<div class="container py-5">
    <div class="image-card">
        <h2>{{ $image['title'] ?? 'Judul Tidak Ada' }}</h2>

        <img src="{{ $image['image_url'] ?? 'https://via.placeholder.com/700x500?text=Gambar+Hilang' }}" 
             alt="{{ $image['title'] ?? 'Gambar' }}"
             onerror="this.src='https://via.placeholder.com/500x350?text=Image+Not+Found'">
        
        <div class="image-content">
            
            <div class="info text-center mt-3">
                @if(!empty($image['categories']['name']))
                    <span class="badge bg-warning text-dark me-2">🏷️ Kategori: {{ $image['categories']['name'] }}</span> 
                @else
                    <span class="badge bg-secondary text-light me-2">🏷️ Kategori: N/A</span> 
                @endif
                <span class="text-white date">
                    • 📅 Diunggah pada {{ Carbon::parse($image['created_at'] ?? now())->format('d M Y, H:i') }}
                </span>
            </div>

            <p class="desc">
                {{ $image['description'] ?? 'Tidak ada deskripsi tersedia.' }}
            </p>
            
            <div class="d-flex justify-content-between align-items-center mb-4 border-top pt-3">
                
                <div class="fw-bold text-light">
                    Oleh: <span class="text-warning">{{ $image['users']['name'] ?? 'Pengguna Artrium' }}</span>
                </div>
                
                {{-- BLOK AKSI KARYA --}}
                <div class="action-buttons-group">
                    
                    @php
                        // Cek apakah pengguna adalah pemilik karya
                        $isOwner = Auth::check() && (Auth::user()->supabase_uuid === ($image['user_id'] ?? null));
                        // Tentukan tujuan tombol Kembali
                        $backRoute = $isOwner ? route('profile.show') : route('gallery.index');
                        $backText = $isOwner ? 'Kembali ke Profil' : 'Kembali';
                    @endphp
                    
                    @if ($isOwner)
                        {{-- HANYA MUNCUL JIKA PEMILIK KARYA --}}
                        <a href="{{ route('images.edit', $image['id']) }}" class="btn btn-warning btn-sm">Edit Karya</a>
                        
                        {{-- TOMBOL HAPUS KARYA (Menggunakan Modal Konfirmasi) --}}
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteImageModal">
                            Hapus
                        </button>
                    @else
                        {{-- TOMBOL LAPORKAN KARYA (Hanya jika bukan pemilik dan sudah login) --}}
                        @auth
                            <button type="button" class="btn btn-danger-outline btn-sm" data-bs-toggle="modal" data-bs-target="#reportModal">
                                🚩 Laporkan Karya
                            </button>
                        @endauth
                    @endif

                    {{-- TOMBOL KEMBALI OTOMATIS --}}
                    <a href="{{ $backRoute }}" class="btn btn-secondary btn-sm">{{ $backText }}</a>
                </div>
            </div>
            
            <div class="comment-area">
                
                {{-- PERBAIKAN: Menggunakan $image['comments'] untuk counter --}}
                <h4 class="text-light mb-3">Komentar ({{ count($image['comments'] ?? []) }})</h4>
                
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
                            {{-- FIX: Tambahkan old('content') untuk mempertahankan input --}}
                            <textarea name="content" class="form-control" rows="2" placeholder="Tulis komentar Anda..." required>{{ old('content') }}</textarea>
                            @if ($errors->has('content'))
                                <div class="text-danger mt-1">{{ $errors->first('content') }}</div>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Kirim Komentar</button>
                    </form>
                @else
                    <p class="text-center text-gray-500">Silakan <a href="{{ route('login') }}" class="text-warning">Login</a> untuk meninggalkan komentar.</p>
                @endauth

                <div id="comments-list">
                    {{-- PERBAIKAN: Menggunakan $image['comments'] untuk loop --}}
                    @forelse ($image['comments'] ?? [] as $comment) 
                        <div class="comment-item">
                            <div class="comment-text-container" style="padding-right: 70px;"> 
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-warning">{{ $comment['users']['name'] ?? 'Pengguna Artrium' }}</strong>
                                    <small class="text-muted">{{ Carbon::parse($comment['created_at'])->diffForHumans() }}</small>
                                </div>
                                <p class="text-light mb-0">{{ $comment['content'] }}</p>
                            </div>
                            
                            {{-- TOMBOL HAPUS KOMENTAR (Menggunakan Modal Konfirmasi) --}}
                            @auth
                                @if (Auth::user()->supabase_uuid === $comment['user_id'])
                                    <button type="button" class="btn-delete-comment" data-bs-toggle="modal" data-bs-target="#deleteCommentModal-{{ $comment['id'] }}">
                                        Hapus
                                    </button>
                                @endif
                            @endauth
                        </div>

                        {{-- MODAL KONFIRMASI HAPUS KOMENTAR --}}
                        <div class="modal fade" id="deleteCommentModal-{{ $comment['id'] }}" tabindex="-1" aria-labelledby="deleteCommentModalLabel-{{ $comment['id'] }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteCommentModalLabel-{{ $comment['id'] }}">Konfirmasi Penghapusan Komentar</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Apakah Anda yakin ingin menghapus komentar ini?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <form action="{{ route('comments.destroy', $comment['id']) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @empty
                        <p class="text-center text-gray-500">Belum ada komentar untuk karya ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================== --}}
{{-- MODAL GLOBAL --}}
{{-- ========================================================== --}}

{{-- 1. MODAL LAPORKAN KARYA (Report Modal) --}}
@auth
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('reports.store', $image['id']) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="reportModalLabel"><i class="fas fa-flag"></i> Laporkan Konten</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Tolong berikan alasan mengapa Anda melaporkan karya **{{ $image['title'] }}**:</p>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Alasan Utama <span class="text-danger">*</span></label>
                        <select class="form-select form-control-dark" id="reason" name="reason" required>
                            <option value="" disabled selected>Pilih salah satu alasan</option>
                            <option value="Pornografi/Konten Seksual">Pornografi/Konten Seksual</option>
                            <option value="Ujaran Kebencian/Diskriminasi">Ujaran Kebencian/Diskriminasi</option>
                            <option value="Kekerasan atau Ancaman">Kekerasan atau Ancaman</option>
                            <option value="Spam atau Penipuan">Spam atau Penipuan</option>
                            <option value="Pelanggaran Hak Cipta">Pelanggaran Hak Cipta</option>
                            <option value="Lainnya">Lainnya (Jelaskan di bawah)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="details" class="form-label">Detail Tambahan (Opsional)</label>
                        <textarea class="form-control form-control-dark" id="details" name="details" rows="3" maxlength="500" placeholder="Berikan detail lebih lanjut tentang pelanggaran."></textarea>
                        <div class="form-text text-muted">Maksimal 500 karakter.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-paper-plane"></i> Kirim Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endauth

{{-- 2. MODAL KONFIRMASI HAPUS KARYA --}}
@if ($isOwner)
<div class="modal fade" id="deleteImageModal" tabindex="-1" aria-labelledby="deleteImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteImageModalLabel">Konfirmasi Hapus Karya</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-danger fw-bold">PERINGATAN:</p>
                <p>Apakah Anda yakin ingin menghapus karya **{{ $image['title'] }}**? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('images.destroy', $image['id']) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Ya, Hapus Karya</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>