@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-warning text-center">
                    <h3 class="fw-bold mb-0">Selamat Datang Kembali!</h3>
                    <small class="text-dark">Masuk ke akun Anda.</small>
                </div>
                <div class="card-body">

                    <!-- FIX KRITIS: MENAMPILKAN SEMUA ERROR VALIDASI DAN SISTEM -->
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <strong class="fw-bold">Mohon periksa kesalahan input atau sistem berikut:</strong>
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
                    <!-- END FIX KRITIS -->

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                                autofocus class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" type="password" name="password" required
                                class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning fw-bold">Masuk</button>
                        </div>
                    </form>

                    <p class="text-center text-muted mt-3 mb-0">
                        Belum punya akun? <a href="{{ route('register') }}" class="text-warning fw-bold">Daftar Sekarang</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <!-- SCRIPT AUTO-REFRESH UNTUK MENGATASI 419 -->
    <script>
        // Fungsi untuk memperingatkan pengguna tentang 419 jika token kedaluwarsa
        function checkCsrfExpiration() {
            // Asumsi sesi default Laravel adalah 120 menit (2 jam).
            // Kita paksa refresh jika form sudah terbuka selama 30 menit (1800 detik)
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