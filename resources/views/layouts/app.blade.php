<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artrium | @yield('title', 'Galeri Alam')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    {{-- Tambahkan Yield untuk CSS custom dari view anak (misalnya show.blade.php) --}}
    @stack('styles') 
    
    <style>
        html, body {
            height: 100%;
            display: flex;
            flex-direction: column;
            background-color: #0A0A0A;
            color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }

        main {
            flex: 1 0 auto;
            padding-bottom: 30px; 
        }

        footer {
            flex-shrink: 0;
            background-color: #0A0A0A;
            color: #F6C74D;
            border-top: 1px solid rgba(246, 199, 77, 0.25);
            text-align: center;
            padding: 10px 0;
            font-size: 0.9rem;
            letter-spacing: 0.3px;
        }

        .card-img-top {
            height: 250px;
            object-fit: cover;
        }

        .navbar {
            background-color: #111 !important;
        }

        .navbar-brand {
            color: #F6C74D !important;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .nav-link, .dropdown-item {
            color: #f8f9fa !important;
        }

        .btn-warning {
            background-color: #F6C74D;
            color: #000;
            font-weight: 600;
            border-radius: 10px;
            border: none;
        }

        .btn-warning:hover {
            background-color: #FFD85C;
            box-shadow: 0 0 10px rgba(246, 199, 77, 0.5);
        }
        
        .dropdown-menu {
             /* Menjaga dropdown tetap gelap */
            --bs-dropdown-bg: #111; 
        }
    </style>
</head>

<body>
    {{-- NAVIGASI UTAMA (Diperbarui agar konsisten) --}}
    <nav class="navbar navbar-expand-lg shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                🌿 Artrium
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center"> {{-- Tambah align-items-center untuk konsistensi --}}
                    
                    {{-- Navigasi Utama --}}
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('gallery.index') }}">Explore</a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('profile.show') }}">Profile</a>
                        </li>
                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-warning btn-sm me-2" href="{{ route('images.create') }}">
                                + Unggah Karya
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Halo, {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item text-light" href="{{ route('profile.show') }}">Lihat Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-outline-light btn-sm me-2" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-warning btn-sm" href="{{ route('register') }}">Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        <div class="container">
            {{-- Notifikasi --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </main>

    <footer>
        © 2025 Artrium Project — Kelompok 6
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>