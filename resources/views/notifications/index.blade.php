<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Saya - Artrium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #0A0A0A;
            color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }

        .notification-card {
            max-width: 800px;
            margin: 60px auto;
            background: rgba(20, 20, 20, 0.9);
            border-radius: 20px;
            padding: 30px;
        }

        .notification-item {
            background-color: rgba(30, 30, 30, 0.9);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            border-left: 4px solid #F6C74D;
        }

        h1 {
            color: #F6C74D;
            font-weight: 700;
            margin-bottom: 30px;
        }

        .text-muted {
            color: #aaa !important;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="notification-card">

            <h1 class="mb-4 text-white">Notifikasi Saya</h1>

            {{-- Error / Success --}}
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- List Notifikasi --}}
            <div class="list-group">

                @forelse ($notifications as $notif)
                    <div
                        class="notification-item d-flex gap-3 p-3 mb-2 rounded 
                    {{ $notif['is_read'] ? 'bg-secondary-subtle' : 'bg-dark border border-light' }}">

                        {{-- Thumbnail Image --}}
                        @if (isset($notif['image']['image_url']))
                            <img src="{{ $notif['image']['image_url'] }}" class="rounded"
                                style="width: 70px; height: 70px; object-fit: cover;">
                        @else
                            <div class="bg-secondary rounded" style="width: 70px; height: 70px;"></div>
                        @endif

                        <div class="flex-grow-1">

                            {{-- Performer --}}
                            <strong class="text-info">
                                {{ $notif['performer']['name'] ?? 'Pengguna' }}
                            </strong>

                            {{-- Message --}}
                            <p class="mb-1 text-light">
                                {{ $notif['message'] ?? 'Notifikasi baru' }}
                            </p>

                            {{-- Waktu --}}
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($notif['created_at'])->diffForHumans() }}
                            </small>

                        </div>

                        {{-- Link ke gambar --}}
                        @if (isset($notif['image_id']))
                            <a href="{{ route('images.show', $notif['image_id']) }}"
                                class="btn btn-sm btn-outline-light">
                                Lihat
                            </a>
                        @endif

                    </div>
                @empty
                    <div class="alert alert-info">Tidak ada notifikasi baru.</div>
                @endforelse

            </div>

            {{-- Tombol Tandai Semua Dibaca --}}
            @if (count($notifications) > 0)
                <form action="{{ route('notifications.read') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif

        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
