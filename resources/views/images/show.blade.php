@php
    // Gunakan Carbon untuk format tanggal jika diperlukan
    $createdAt = $image['created_at'] ?? now();
    $formattedDate = \Carbon\Carbon::parse($createdAt)->format('d M Y, H:i');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $image['title'] ?? 'Detail Karya' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    
    <style>
        /* CSS yang Anda buat di sini */
        body { background-color: #0A0A0A; color: #f8f9fa; font-family: 'Poppins', sans-serif; }
        .image-card { max-width: 750px; margin: 60px auto; background: rgba(20, 20, 20, 0.9); border: 1px solid rgba(246, 199, 77, 0.25); border-radius: 20px; box-shadow: 0 0 35px rgba(246, 199, 77, 0.15); backdrop-filter: blur(10px); text-align: center; padding: 40px; }
        .image-card h2 { color: #F6C74D; font-weight: 700; margin-bottom: 20px; }
        .image-card img { max-width: 100%; border-radius: 15px; box-shadow: 0 0 25px rgba(246, 199, 77, 0.25); margin-bottom: 25px; transition: all 0.3s ease; object-fit: cover; }
        .image-card img:hover { transform: scale(1.02); box-shadow: 0 0 35px rgba(246, 199, 77, 0.4); }
        .info { color: #c9a94a; font-size: 0.95rem; margin-bottom: 10px; }
        .desc { color: #ddd; font-size: 1rem; margin-top: 10px; }
        .btn-warning { background-color: #F6C74D; border: none; color: #0A0A0A; font-weight: 700; border-radius: 12px; padding: 10px 20px; transition: all 0.3s ease; box-shadow: 0 0 10px rgba(246, 199, 77, 0.3); }
        .btn-warning:hover { background-color: #FFD85C; box-shadow: 0 0 20px rgba(246, 199, 77, 0.5); }
        .btn-secondary { background-color: rgba(255, 255, 255, 0.1); border: none; color: #f8f9fa; font-weight: 600; border-radius: 12px; padding: 10px 20px; transition: all 0.3s ease; }
        .btn-secondary:hover { background-color: rgba(255, 255, 255, 0.2); }
        .date { color: #aaa; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="container py-5">
    
    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show text-dark" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show text-dark" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="image-card">
        <h2>{{ $image['title'] ?? 'Detail Gambar' }}</h2>

        {{-- Gambar dari Supabase --}}
        {{-- Menggunakan image_path yang benar dari Controller --}}
        <img src="{{ env('SUPABASE_URL') }}/storage/v1/object/public/images/{{ $image['image_path'] ?? '' }}" 
             alt="{{ $image['title'] ?? '' }}"
             onerror="this.src='https://via.placeholder.com/500x350?text=Image+Not+Found'">

        {{-- Informasi detail --}}
        <div class="info">
            {{-- Menggunakan category_id jika data Supabase mengirim ID --}}
            🏷️ {{ $image['category'] ?? 'N/A' }}
            @if(!empty($image['location'])) • 📍 {{ $image['location'] }} @endif
        </div>

        <div class="desc">
            {{ $image['description'] ?? 'Tidak ada deskripsi tersedia.' }}
        </div>

        <div class="date">
            📅 Diunggah pada {{ $formattedDate }}
        </div>

        {{-- =================================================== --}}
        {{-- 🔧 TOMBOL AKSI (CRUD & REPORT) --}}
        {{-- =================================================== --}}
        <div class="mt-4 d-flex justify-content-center gap-3">
            
            {{-- Aksi Edit & Delete (Hanya Pemilik/Admin) --}}
            @auth
                @if (Auth::id() == ($image['user_id'] ?? null))
                    {{-- Tombol Edit (UPDATE - Tugas Anda) --}}
                    <a href="{{ route('images.edit', $image['id']) }}" class="btn btn-warning">Edit Karya</a>
                    
                    {{-- Tombol Delete (DELETE - Tugas Daffa) --}}
                    <form action="{{ route('images.destroy', $image['id']) }}" method="POST" onsubmit="return confirm('ANDA YAKIN INGIN MENGHAPUS KARYA INI?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-secondary" style="background-color: #dc3545; color: #fff;">Hapus</button>
                    </form>
                @else
                    {{-- 📢 Tombol Report (REPORT - Tugas Anda) --}}
                    <button type="button" 
                            class="btn btn-secondary" 
                            data-bs-toggle="modal" 
                            data-bs-target="#reportModal">
                        Laporkan Konten Tidak Sesuai
                    </button>
                @endif
            @endauth

            {{-- Tombol Kembali --}}
            <a href="{{ route('gallery.index') }}" class="btn btn-secondary">Kembali ke Galeri</a>
        </div>
    </div>
</div>

{{-- MODAL FORM PELAPORAN KONTEN (REPORT) - Wajib ada di sini --}}
@auth
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #1f2937;">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="reportModalLabel">Laporkan Karya Ini</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            {{-- Form akan POST ke route images.report --}}
            <form method="POST" action="{{ route('images.report', $image['id'] ?? '') }}">
                @csrf
                <div class="modal-body text-dark">
                    
                    {{-- Hidden input untuk ID Gambar yang dilaporkan --}}
                    <input type="hidden" name="image_id" value="{{ $image['id'] ?? '' }}">
                    
                    <div class="mb-3">
                        <label for="reason_category" class="form-label text-white">Kategori Pelanggaran</label>
                        <select name="reason_category" id="reason_category" class="form-select" required>
                            <option value="">Pilih Alasan</option>
                            <option value="Plagiat">Plagiat / Klaim Hak Cipta</option>
                            <option value="Non-Tema">Tidak Sesuai Tema Alam</option>
                            <option value="SARA">Konten SARA / Ujaran Kebencian</option>
                            <option value="Lainnya">Alasan Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label text-white">Detail Laporan (Opsional)</label>
                        <textarea name="description" id="description" rows="3" class="form-control"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Kirim Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>