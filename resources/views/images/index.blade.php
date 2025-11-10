@extends('layouts.app') 

@section('title', 'Galeri Foto Alam')

@section('content')

    <div class="bg-light p-5 mb-5 text-center border rounded">
        <h1 class="display-5 fw-bold text-dark mb-3">Temukan Bidikan Alam.</h1>
        <p class="lead text-muted mx-auto" style="max-width: 600px;">
            Dari gunung hingga lautan, setiap foto menyimpan kisah tentang dunia yang menakjubkan.
        </p>
        
        <form action="{{ route('gallery.index') }}" method="GET" class="d-flex justify-content-center mt-4 mx-auto" style="max-width: 600px;">
            <input type="text" name="search" class="form-control me-2" placeholder="Cari Inspirasimu..." value="{{ request('search') }}">
            
            <select name="category" class="form-select me-2">
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
            <div class="mt-3">
                <small class="text-muted">
                    Hasil pencarian: 
                    @if(request('search'))
                        "{{ request('search') }}"
                    @endif
                    @if(request('search') && request('category'))
                        dan 
                    @endif
                    @if(request('category'))
                        kategori "{{ request('category') }}"
                    @endif
                    ({{ count($images) }} hasil)
                </small>
            </div>
        @endif
    </div>

    <h2 class="mb-4 text-center">Karya Alam dari Lensa Mereka</h2>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mt-4">
        
        @forelse($images as $image)
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <a href="{{ route('images.show', $image['id']) }}">
                        <img src="{{ $image['image_url'] }}" 
                             alt="{{ $image['title'] }}" 
                             class="card-img-top" 
                             loading="lazy"
                             style="height: 200px; object-fit: cover;"
                             onerror="this.src='https://via.placeholder.com/300x200?text=Image+Error'">
                    </a>

                    <div class="card-body">
                        <h5 class="card-title text-truncate">{{ $image['title'] }}</h5>
                        <p class="card-text text-muted mb-1">
                            <small>
                                @if(isset($image['category']) && !empty($image['category']))
                                    🏷️ {{ $image['category'] }} | 
                                @endif
                                📅 {{ \Carbon\Carbon::parse($image['created_at'])->format('d M Y') }}
                            </small>
                        </p>
                        <div class="mt-2">
                            <a href="{{ route('images.edit', $image['id']) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                            <a href="{{ route('images.show', $image['id']) }}" class="btn btn-sm btn-outline-primary">View</a>
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

@endsection