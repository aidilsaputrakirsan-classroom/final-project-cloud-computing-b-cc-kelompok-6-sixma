<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Saya - Artrium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0A0A0A; color: #f8f9fa; font-family: 'Poppins', sans-serif; }
        .notification-card { max-width: 800px; margin: 60px auto; background: rgba(20, 20, 20, 0.9); border-radius: 20px; padding: 30px; }
        .notification-item { background-color: rgba(30, 30, 30, 0.9); padding: 15px; border-radius: 10px; margin-bottom: 10px; border-left: 4px solid #F6C74D; }
        h1 { color: #F6C74D; font-weight: 700; margin-bottom: 30px; }
        .text-muted { color: #aaa !important; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="notification-card">
        <h1>Notifikasi Saya</h1>
        
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="list-group">
            @forelse ($notifications as $notification)
                <div class="notification-item d-flex justify-content-between align-items-start">
                    <div>
                        {{-- Asumsi kolom 'message' ada di tabel notifications --}}
                        <p class="mb-1 text-light">{{ $notification['message'] ?? 'Notifikasi baru' }}</p>
                        {{-- Asumsi kolom 'created_at' ada --}}
                        <small class="text-muted">
                            {{ \Carbon\Carbon::parse($notification['created_at'] ?? now())->diffForHumans() }}
                        </small>
                    </div>
                    {{-- Tambahkan link aksi jika ada, misalnya link ke karya --}}
                </div>
            @empty
                <div class="alert alert-info">Tidak ada notifikasi baru.</div>
            @endforelse
        </div>
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>