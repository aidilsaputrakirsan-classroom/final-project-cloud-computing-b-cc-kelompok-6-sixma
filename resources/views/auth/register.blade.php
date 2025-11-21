@extends('layouts.app') 

@section('title', 'Register')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-warning text-center">
                <h3 class="fw-bold mb-0">Buat Akun Baru</h3>
                <small class="text-dark">Daftar sekarang untuk menjelajahi halaman kami.</small>
            </div>
            <div class="card-body">
                <!-- 🚨 PENAMBAHAN 1: MENAMPILKAN ERROR GLOBAL -->
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <!-- Ambil pesan error pertama yang bukan terkait input spesifik -->
                        @if ($errors->has('error'))
                            {{ $errors->first('error') }}
                        @elseif ($errors->has('email') && !$errors->has('name') && !$errors->has('password'))
                            {{ $errors->first('email') }}
                        @else
                            Mohon periksa kembali input Anda.
                        @endif
                    </div>
                @endif
                <!-- END PENAMBAHAN 1 -->
                
                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama/Username</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                            class="form-control @error('name') is-invalid @enderror">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            class="form-control @error('email') is-invalid @enderror">
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

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <!-- 🚨 PENAMBAHAN 2: Menambahkan is-invalid agar error tampil -->
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                            class="form-control @error('password') is-invalid @enderror">
                        <!-- NOTE: Error untuk confirmation akan ditampilkan di input password di atas, 
                             namun kelas is-invalid di sini penting agar terlihat merah saat konfirmasi salah. -->
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning fw-bold">Daftar</button>
                    </div>
                </form>
                
                <p class="text-center text-muted mt-3 mb-0">
                    Sudah punya akun? <a href="{{ route('login') }}" class="text-warning fw-bold">Login</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection