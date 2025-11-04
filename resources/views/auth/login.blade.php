@extends('layouts.app') 

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-900" style="background-image: url('{{ asset('images/login-bg.jpg') }}'); background-size: cover;">
    <div class="w-full max-w-sm bg-gray-800 p-8 rounded-lg shadow-2xl">
        
        <h2 class="text-3xl font-bold text-center text-yellow-400 mb-2">Selamat Datang Kembali!</h2>
        <p class="text-center text-gray-400 text-sm mb-6">Masuk ke akun Anda untuk menjelajahi dunia melalui karya.</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4">
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-3 bg-gray-700 text-white border border-gray-600 rounded-md focus:outline-none focus:border-yellow-400 placeholder-gray-500"
                    placeholder="Masukkan Email">
                @error('email')
                    <span class="text-red-400 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <input id="password" type="password" name="password" required
                    class="w-full px-4 py-3 bg-gray-700 text-white border border-gray-600 rounded-md focus:outline-none focus:border-yellow-400 placeholder-gray-500"
                    placeholder="Masukkan Password">
                @error('password')
                    <span class="text-red-400 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="text-right mb-6">
                <a href="#" class="text-sm text-yellow-400 hover:text-yellow-300">Forgot Password?</a>
            </div>

            <button type="submit"
                class="w-full py-3 bg-yellow-500 text-gray-900 font-bold rounded-md hover:bg-yellow-600 transition duration-200">
                Masuk
            </button>
        </form>
        
        <p class="text-center text-gray-400 text-sm mt-4">
            Belum punya akun? 
            <a href="{{ route('register') }}" class="text-yellow-400 font-bold hover:text-yellow-300">Daftar sekarang</a>
        </p>

    </div>
</div>
@endsection