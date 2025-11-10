@extends('layouts.app')

@section('title', 'Galeri Foto Alam')

@section('content')
<style>
    /* ======== TEMA UTAMA ======== */
    body {
        background-color: #0A0A0A !important;
        color: #f8f9fa !important;
        font-family: 'Poppins', sans-serif;
    }

    /* ======== HERO SECTION ======== */
    .hero-section {
        background: linear-gradient(160deg, #111111, #1a1a1a);
        border: 1px solid rgba(246, 199, 77, 0.25);
        border-radius: 24px;
        padding: 60px 40px;
        text-align: center;
        color: #f8f9fa;
        box-shadow: 0 0 40px rgba(246, 199, 77, 0.1);
        transition: all 0.4s ease;
        margin-top: 20px;
    }

    .hero-section:hover {
        box-shadow: 0 0 50px rgba(246, 199, 77, 0.25);
        transform: translateY(-3px);
    }

    .hero-section h1 {
        color: #F6C74D;
        font-weight: 800;
        font-size: 2.8rem;
        letter-spacing: 1px;
    }

    .hero-section p {
        color: #d0d0d0;
        font-size: 1.1rem;
        max-width: 650px;
        margin: 10px auto 25px;
    }

    /* ======== FORM CARI ======== */
    .hero-section form {
        max-width: 700px;
        margin: 0 auto;
    }

    .form-control,
    .form-select {
        background-color: #121212;
        color: #fff;
        border: 1px solid #2e2e2e;
        border-radius: 12px;
        transition: all 0.3s ease;
        height: 45px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #F6C74D;
        box-shadow: 0 0 10px rgba(246, 199, 77, 0.4);
    }

    .form-control::placeholder {
        color: #c9a94a;
    }

    .btn-warning {
        background-color: #F6C74D;
        border: none;
        color: #0A0A0A;
        font-weight: 700;
        border-radius: 12px;
        transition: all 0.3s ease;
        padding: 0 25px;
        height: 45px;
    }

    .btn-warning:hover {
        background-color: #FFD85C;
        box-shadow: 0 0 20px rgba(246, 199, 77, 0.5);
    }

    .btn-outline-secondary {
        border-color: #555;
        color: #ccc;
        border-radius: 12px;
        height: 45px;
        transition: 0.3s;
    }

    .btn-outline-secondary:hover {
        background-color: #2c2c2c;
        color: #fff;
        border-color: #777;
    }

    /* ======== HASIL PENCARIAN ======== */
    .search-info small {
        color: #c9a94a;
        font-size: 0.95rem;
    }

    /* ======== GRID GALERI ======== */
    h2 {
        color: #F6C74D;
        font-weight: 700;
        text-align: center;
        margin-top: 70px;
        margin-bottom: 30px;
        letter-spacing: 0.8px;
        font-size: 1.8rem;
    }

    .card {
        background: #151515;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 15px;
        color: #fff;
        transition: all 0.35s ease;
        overflow: hidden;
    }

    .card:hover {
        transform: translateY(-8px);
        box-shadow: 0 0 25px rgba(246, 199, 77, 0.3);
    }

    .card img {
        transition: all 0.3s ease;
    }

    .card:hover img {
        transform: scale(1.05);
        opacity: 0.9;
    }

    .card-title {
        color: #F6C74D;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .card-text small {
        color: #aaa;
    }

    /* ======== TOMBOL DALAM CARD ======== */
    .btn-outline-warning {
        border-color: #F6C74D;
        color: #F6C74D;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-outline-warning:hover {
        background-color: #F6C74D;
        color: #0A0A0A;
    }

    .btn-outline-primary {
        border-color: #777;
        color: #ccc;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background-color: #2e2e2e;
        color: #fff;
    }

    /* ======== PESAN KOSONG ======== */
    .lead.text-muted {
        color: #bbb !important;
    }

    .btn-primary {
        background-color: #F6C74D;
        border: none;
        color: #0A0A0A;
        font-weight: 600;
        border-radius: 10px;
    }

    .btn-primary:hover {
        background-color: #FFD85C;
    }

    /* ======== FLOATING UPLOAD BUTTON ======== */
    .floating-upload-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #F6C74D;
        color: #fff;
        font-size: 2.2rem;
        font-weight: 700;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        box-shadow: 0 0 20px rgba(246, 199, 77, 0.4);
        transition: all 0.3s ease;
        z-index: 999;
        text-decoration: none;
    }

    .floating-upload-btn:hover {
        background: #FFD85C;
        transform: scale(1.1);
        box-shadow: 0 0 30px rgba(246, 199, 77, 0.6);
        color: #fff;
    }
</style>

<div class="hero-section">
    <h1>Temukan Bidikan Alam.</h1>
    <p>Dari gunung hingga lautan, setiap foto menyimpan kisah tentang dunia yang menakjubkan.</p>

    <form action="{{ route('gallery.index') }}" method="GET" class="d-flex justify-content-center flex-wrap gap-2">
        <input type="text" name="search" class="form-control me-2 flex-grow-1" placeholder="Cari Inspirasimu..." value="{{ request('search') }}">
        
        <select name="category" class="form-select me-2" style="max-width: 200px;">
            <option value="">Semua Kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                    {{ $category }}
                </option>
            @endforeach
        </select>
        
        <button type="submit" class="btn btn-warning me-2">Cari</button>
        <a href="{{ route('gallery.index') }}" class="btn btn-outline-secondary">Reset</a>
    </form>

    @if(request()->has('search') || request()->has('category'))
        <div class="mt-3 search-info">
            <small>
                Hasil pencarian:
                @if(request('search')) "{{ request('search') }}" @endif
                @if(request('search') && request('category')) dan @endif
                @if(request('category')) kategori "{{ request('category') }}" @endif
                ({{ count($images) }} hasil)
            </small>
        </div>
    @endif
</div>

<h2>Karya Alam dari Lensa Mereka</h2>

<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mt-4">
    @forelse($images as $image)
        <div class="col">
            <div class="card h-100 shadow-sm border-0">
                <a href="{{ route('images.show', $image['id']) }}">
                    <img src="{{ $image['image_url'] }}" 
                         alt="{{ $image['title'] }}" 
                         class="card-img-top" 
                         loading="lazy"
                         style="height: 230px; object-fit: cover;"
                         onerror="this.src='https://via.placeholder.com/300x200?text=Image+Error'">
                </a>

                <div class="card-body text-center">
                    <h5 class="card-title text-truncate">{{ $image['title'] }}</h5>
                    <p class="card-text mb-2">
                        <small>
                            @if(isset($image['category']) && !empty($image['category']))
                                🏷️ {{ $image['category'] }} |
                            @endif
                            📅 {{ \Carbon\Carbon::parse($image['created_at'])->format('d M Y') }}
                        </small>
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('images.edit', $image['id']) }}" class="btn btn-sm btn-outline-warning px-3">Edit</a>
                        <a href="{{ route('images.show', $image['id']) }}" class="btn btn-sm btn-outline-primary px-3">View</a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <p class="lead text-muted">
                @if(request()->has('search') || request()->has('category'))
                    Tidak ada hasil untuk pencarian Anda.
                @else
                    Belum ada karya yang diunggah.
                @endif
            </p>
            <a href="{{ route('images.create') }}" class="btn btn-primary">Unggah Karya Pertamamu!</a>
        </div>
    @endforelse
</div>

<!-- 🔸 Floating Quick Upload Button -->
<a href="{{ route('images.create') }}" class="floating-upload-btn">+</a>

@endsection
