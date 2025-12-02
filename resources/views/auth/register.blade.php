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
                
                <!-- MENAMPILKAN SEMUA ERROR VALIDASI SPESIFIK -->
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

                <!-- MENAMPILKAN ERROR SISTEM/EXCEPTION (DARI Controller) -->
                @if (session('error'))
                    <div class="alert alert-danger" role="alert">
                        <strong class="fw-bold">Error Sistem:</strong>
                        <p class="mb-0">{{ session('error') }}</p>
                    </div>
                @endif
                
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
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                            class="form-control @error('password') is-invalid @enderror">
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