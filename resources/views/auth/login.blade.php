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
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
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
@endsection