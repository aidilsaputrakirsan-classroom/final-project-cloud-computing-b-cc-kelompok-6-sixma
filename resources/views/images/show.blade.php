<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $image['title'] ?? 'Detail Karya' }} - Detail Karya</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    {{-- Tambahkan Font Awesome untuk Ikon --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    @php use Carbon\Carbon; @endphp
    
    <script src="https://cdn.tailwindcss.com"></script> 
    
    <style>
        /* ======== STYLING CSS UTAMA & FONT FIX ======== */
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
        
        /* Judul di tengah card */
        .image-card h2 {
            font-weight: 700;
            color: #F6C74D;
            margin-bottom: 25px;
            text-align: center;
        }

        /* FIX FINAL: Info Kategori dan Tanggal */
        .info {
            padding-bottom: 8px; 
            margin-bottom: 8px; 
            border-bottom: 1px solid rgba(246, 199, 77, 0.1);
        }

        /* FIX FINAL: Deskripsi dan Jarak */
        .desc {
            margin-top: 10px; 
            margin-bottom: 15px; 
            font-size: 1.05rem;
        }
        
        /* FIX: Blok aksi utama */
        .action-buttons-group {
             gap: 12px; 
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

        /* Gaya Tombol Umum */
        .btn-warning, .btn-secondary, .btn-danger, .btn-danger-outline {
             font-weight: 600;
             border-radius: 8px;
             padding: 8px 15px;
             transition: all 0.2s ease;
             font-size: 0.9rem;
        }

        .btn-warning {
            background-color: #F6C74D;
            border: none;
            color: #0A0A0A;
        }
        .btn-secondary {
            background-color: rgba(255, 255, 255, 0.15); 
            border: none;
            color: #f8f9fa;
        }
        .btn-danger { 
            background-color: #dc3545;
            border: none;
            color: #fff;
        }
        /* Gaya Tombol Report (btn-danger-outline) */
        .btn-danger-outline { 
            background-color: rgba(255, 255, 255, 0.15); 
            border: none; 
            color: #dc3545; 
        }
        .btn-danger-outline:hover {
             background-color: rgba(220, 53, 69, 0.1);
             color: #fff;
        }


        /* Tombol Hapus Komentar (Di dalam card) */
        .btn-delete-comment {
            background-color: #dc3545; 
            color: #fff; 
            border: none;
            border-radius: 8px; 
            padding: 4px 10px; 
            font-size: 0.8rem; 
            
            position: absolute; 
            top: 50%; 
            right: 12px;
            transform: translateY(-50%); 
        }

        /* Input Komentar */
         .comment-input-form textarea {
            background: rgba(40, 40, 40, 0.9);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            resize: none;
        }
        
        /* FIX: Agar tombol aksi tidak terlalu tinggi dari tulisan "Oleh" */
        .d-flex.justify-content-between.align-items-center.mb-4.pt-3 {
             padding-top: 10px !important; 
        }

        /* ======== STYLING MODAL REPORT (DARK THEME) ======== */
        .modal-content {
            background-color: rgba(30, 30, 30, 1); /* Background gelap */
            color: white;
            border-radius: 12px;
            border: 1px solid rgba(246, 199, 77, 0.15);
        }
        .modal-header {
             border-bottom-color: rgba(246, 199, 77, 0.15) !important;
        }
        .modal-footer {
             border-top-color: rgba(246, 199, 77, 0.15) !important;
        }

        /* Styling Form Control Gelap */
        .form-control-dark {
            background-color: rgba(40, 40, 40, 1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .form-control-dark:focus {
            background-color: rgba(40, 40, 40, 1);
            color: white;
            border-color: #F6C74D;
            box-shadow: 0 0 0 0.25rem rgba(246, 199, 77, 0.25);
        }
        .form-control-dark::placeholder {
            color: #9ca3af;
        }
        .form-select.form-control-dark {
             /* Perbaiki bug panah di select */
             background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
             background-color: rgba(40, 40, 40, 1);
        }
        .btn-close-white {
             filter: invert(1) grayscale(100%) brightness(200%);
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
                <span class="badge bg-warning text-dark me-2">🏷️ Kategori: {{ $image['category_name'] ?? 'N/A' }}</span> 
                <span class="text-white date">
                    • 📅 Diunggah pada {{ Carbon::parse($image['created_at'] ?? now())->format('d M Y, H:i') }}
                </span>
            </div>

            <p class="desc">
                {{ $image['description'] ?? 'Tidak ada deskripsi tersedia.' }}
            </p>
            
            {{-- BLOK UTAMA AKSI --}}
            <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
                
                <div class="fw-bold text-light">
                    Oleh: <span class="text-warning">{{ $image['users']['name'] ?? 'Pengguna Artrium' }}</span>
                </div>
                
                {{-- BLOK AKSI KARYA --}}
                <div class="action-buttons-group">
                    
                    @php
                        $isOwner = Auth::check() && (Auth::user()->supabase_uuid === ($image['user_id'] ?? null));
                        $backRoute = $isOwner ? route('profile.show') : route('gallery.index');
                        $backText = $isOwner ? 'Kembali ke Profil' : 'Kembali';
                    @endphp
                    
                    @if ($isOwner)
                        <a href="{{ route('images.edit', $image['id']) }}" class="btn btn-warning btn-sm">Edit Karya</a>
                        
                        {{-- TOMBOL HAPUS KARYA (Picu Modal Konfirmasi) --}}
                         <button type="button" 
                                 class="btn btn-danger btn-sm" 
                                 data-bs-toggle="modal" 
                                 data-bs-target="#deleteConfirmModal" 
                                 data-form-target="#delete-image-form"
                                 data-delete-type="karya"
                                 data-delete-name="{{ $image['title'] ?? 'ini' }}">
                            Hapus
                        </button>
                        
                    @else
                        {{-- TOMBOL LAPORKAN KARYA (Sekarang terlihat dan styling benar) --}}
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
            
            {{-- FORM HAPUS KARYA YANG TERSEMBUNYI --}}
            @if ($isOwner)
                <form id="delete-image-form" action="{{ route('images.destroy', $image['id']) }}" method="POST" style="display:none;">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
            
            <div class="comment-area">
                
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
                    @forelse ($image['comments'] ?? [] as $comment) 
                        <div class="comment-item position-relative"> 
                            <div class="comment-text-container" style="padding-right: 70px;"> 
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-warning">{{ $comment['users']['name'] ?? 'Pengguna Artrium' }}</strong>
                                    <small class="text-muted">{{ Carbon::parse($comment['created_at'])->diffForHumans() }}</small>
                                </div>
                                <p class="text-light mb-0">{{ $comment['content'] }}</p>
                            </div>
                            
                            {{-- TOMBOL HAPUS KOMENTAR (Picu Modal Konfirmasi) --}}
                            @auth
                                @if (Auth::user()->supabase_uuid === $comment['user_id'])
                                    <button type="button" 
                                            class="btn-delete-comment"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteConfirmModal" 
                                            data-form-target="#delete-comment-form-{{ $comment['id'] }}"
                                            data-delete-type="komentar"
                                            data-delete-name="dari {{ $comment['users']['name'] ?? 'Anda' }}">
                                        Hapus
                                    </button>
                                    
                                    {{-- FORM HAPUS KOMENTAR YANG TERSEMBUNYI --}}
                                    <form id="delete-comment-form-{{ $comment['id'] }}" 
                                          action="{{ route('comments.destroy', $comment['id']) }}" 
                                          method="POST" 
                                          style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endif
                            @endauth
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
            <form id="report-form" action="{{ route('reports.store', $image['id']) }}" method="POST">
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

{{-- 2. MODAL KONFIRMASI HAPUS KUSTOM (Untuk Karya dan Komentar) --}}
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-gray-800 text-white border-red-500 border-2">
            <div class="modal-header border-b border-gray-700">
                <h5 class="modal-title text-xl font-bold text-red-400" id="deleteConfirmModalLabel">Konfirmasi Penghapusan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-lg">Apakah Anda yakin ingin menghapus <span id="delete-target-type" class="font-bold"></span>?</p>
                <p class="text-sm text-gray-400 mt-2">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer border-t border-gray-700">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">Ya, Hapus</button>
                
                {{-- Placeholder untuk form yang akan di-submit --}}
                <input type="hidden" id="delete-form-id">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteConfirmModalElement = document.getElementById('deleteConfirmModal');
        const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
        let currentFormTargetId = null;
        
        // Fungsi untuk menghapus semua backdrop yang tersisa (Final Fix)
        const removeAllBackdrops = () => {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        };

        // 1. Setup Data saat modal dipicu
        deleteConfirmModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            currentFormTargetId = button.getAttribute('data-form-target');
            const deleteType = button.getAttribute('data-delete-type');
            const deleteName = button.getAttribute('data-delete-name');
            
            const modalTitle = deleteConfirmModalElement.querySelector('#deleteConfirmModalLabel');
            const modalBodyType = deleteConfirmModalElement.querySelector('#delete-target-type');
            
            // Mengisi judul dan tipe penghapusan
            if (deleteType === 'komentar') {
                modalTitle.textContent = 'Konfirmasi Penghapusan Komentar';
                modalBodyType.textContent = 'komentar ' + deleteName;
            } else { // Jika hapus Karya
                modalTitle.textContent = 'Konfirmasi Penghapusan Karya';
                modalBodyType.textContent = 'karya ini';
            }
        });

        // 2. Event saat tombol "Ya, Hapus" di dalam modal ditekan
        confirmDeleteBtn.addEventListener('click', function () {
            if (currentFormTargetId) {
                const targetForm = document.querySelector(currentFormTargetId);
                
                // 1. Sembunyikan modal secara manual
                const bsModal = bootstrap.Modal.getInstance(deleteConfirmModalElement);
                if (bsModal) {
                    bsModal.hide(); 
                }

                // 2. Hapus semua backdrop dan class modal-open secara paksa
                removeAllBackdrops();

                // 3. Submit form DELETE
                if (targetForm) {
                    targetForm.submit();
                } else {
                    console.error("Target form tidak ditemukan:", currentFormTargetId);
                }
            }
        });

        // FIX: Pastikan modal-open dihapus saat halaman dimuat ulang jika ada sisa
        window.addEventListener('load', removeAllBackdrops);

    });
</script>
</body>
</html>