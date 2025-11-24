@extends('layouts.app') 

@section('title', 'Register')

@section('content')
<div class="container-fluid vh-0 p-0">
    <div class="row g-0 h-100">
        <!-- Left Side - Register Form -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center bg-dark text-white p-5">
            <div class="register-container" style="max-width: 450px; width: 100%;">
                <!-- Logo/Brand -->
                <h2 class="fw-bold mb-5" style="color: #F4C430;">Artrium.</h2>
                
                <!-- Welcome Text -->
                <div class="mb-4">
                    <h1 class="fw-bold mb-3" style="color: #F4C430; font-size: 2.5rem; line-height: 1.2;">
                        Daftar Sekarang!
                    </h1>
                    <p class="text-white mb-4" style="line-height: 1.6;">
                        Buat akun baru untuk mulai menjelajahi<br>dunia melalui lensa.
                    </p>
                </div>

                <!-- MENAMPILKAN SEMUA ERROR VALIDASI DAN SISTEM -->
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <strong class="fw-bold">Mohon periksa kesalahan input berikut:</strong>
                        <ul class="mt-1 mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- MENAMPILKAN ERROR SESSION (JIKA ADA) -->
                @if (session('error'))
                    <div class="alert alert-danger" role="alert">
                        <strong class="fw-bold">Error Sistem:</strong>
                        <p class="mb-0">{{ session('error') }}</p>
                    </div>
                @endif

                <!-- MENAMPILKAN SUCCESS MESSAGE -->
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        <p class="mb-0">{{ session('success') }}</p>
                    </div>
                @endif

                <!-- Register Form -->
                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Username -->
                    <div class="mb-3">
                        <label for="name" class="form-label text-warning fw-semibold">Username</label>
                        <input id="name" 
                               type="text" 
                               name="name" 
                               value="{{ old('name') }}" 
                               placeholder="Masukkan Username" 
                               required 
                               autofocus
                               class="form-control bg-light border-0 @error('name') is-invalid @enderror"
                               style="padding: 12px 16px; border-radius: 10px; font-size: 0.95rem;">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label text-warning fw-semibold">Email</label>
                        <input id="email" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="Masukkan Email" 
                               required
                               class="form-control bg-light border-0 @error('email') is-invalid @enderror"
                               style="padding: 12px 16px; border-radius: 10px; font-size: 0.95rem;">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label text-warning fw-semibold">Password</label>
                        <input id="password" 
                               type="password" 
                               name="password" 
                               placeholder="Masukkan Password" 
                               required
                               class="form-control bg-light border-0 @error('password') is-invalid @enderror"
                               style="padding: 12px 16px; border-radius: 10px; font-size: 0.95rem;">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label text-warning fw-semibold">Confirm Password</label>
                        <input id="password_confirmation" 
                               type="password" 
                               name="password_confirmation" 
                               placeholder="Konfirmasi Password" 
                               required
                               class="form-control bg-light border-0"
                               style="padding: 12px 16px; border-radius: 10px; font-size: 0.95rem;">
                    </div>

                    <div class="d-grid">
                        <button type="submit" 
                                class="btn btn-warning fw-bold text-dark py-3" 
                                style="border-radius: 10px; font-size: 1.1rem; letter-spacing: 0.5px;">
                            Daftar
                        </button>
                    </div>
                </form>

                <!-- Login Link -->
                <p class="text-center text-white mt-4 mb-0" style="font-size: 0.95rem;">
                    Apakah anda sudah memiliki akun? 
                    <a href="{{ route('login') }}" class="text-warning text-decoration-none fw-semibold">
                        Login
                    </a>
                </p>
            </div>
        </div>

        <!-- Right Side - Image -->
        <div class="col-lg-6 d-none d-lg-block position-relative" 
             style="background: linear-gradient(135deg, #8B9DC3 0%, #B8C5E0 100%);">
            <div class="position-absolute bottom-0 end-0 w-100 h-100">
                <img src="https://images.unsplash.com/photo-1663198265890-8499f5324478?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" 
                     alt="Ferris Wheel" 
                     class="img-fluid w-100 h-100" 
                     style="object-fit: cover; object-position: center;">
            </div>
        </div>
    </div>
</div>

<style>
    /* Remove default body padding/margin */
    body {
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }
    
    /* Custom scrollbar untuk halaman */
    ::-webkit-scrollbar {
        width: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #1a1a1a;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #F4C430;
        border-radius: 4px;
    }
    
    /* Form control focus state */
    .form-control:focus {
        box-shadow: 0 0 0 3px rgba(244, 196, 48, 0.25);
        border-color: #F4C430;
        background-color: #fff;
    }
    
    /* Button hover effect */
    .btn-warning:hover {
        background-color: #E5B730;
        border-color: #E5B730;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(244, 196, 48, 0.4);
        transition: all 0.3s ease;
    }
    
    .btn-warning:active {
        transform: translateY(0);
    }
    
    /* Link hover effect */
    a.text-warning:hover {
        color: #FDD835 !important;
        text-decoration: underline !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 991.98px) {
        .register-container {
            max-width: 100% !important;
            padding: 2rem 1rem;
        }
        
        h1.fw-bold {
            font-size: 2rem !important;
        }
        
        .col-lg-6.bg-dark {
            min-height: 100vh;
        }
    }
    
    @media (max-height: 800px) {
        .register-container {
            padding: 1rem 0;
        }
        
        h2.fw-bold.mb-5 {
            margin-bottom: 2rem !important;
        }
        
        .mb-4 {
            margin-bottom: 1.5rem !important;
        }
    }
    
    /* Alert styling untuk error/success messages */
    .alert {
        border-radius: 10px;
        border: none;
        font-size: 0.9rem;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
    }
</style>

<!-- SCRIPT AUTO-REFRESH UNTUK MENGATASI 419 -->
<script>
    // Fungsi untuk memperingatkan pengguna tentang 419 jika token kedaluwarsa
    function checkCsrfExpiration() {
        const formTime = Date.now() / 1000;
        const formTimestamp = {{ time() }}; // Waktu form dimuat
        const maxTime = 1800; // 30 menit

        if (formTime - formTimestamp > maxTime) {
            alert("Sesi keamanan (CSRF) Anda telah berakhir. Halaman akan dimuat ulang.");
            window.location.reload();
        }
    }
    // checkCsrfExpiration(); // Di nonaktifkan karena sering memicu alert
</script>
@endsection