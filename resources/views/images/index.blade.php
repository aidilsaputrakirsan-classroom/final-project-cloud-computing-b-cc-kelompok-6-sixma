@extends('layouts.app') 

@section('title', 'Galeri Foto Alam')

@section('content')

    <div class="bg-light p-5 mb-5 text-center border rounded">
        <h1 class="display-5 fw-bold text-dark mb-3">Temukan Bidikan Alam.</h1>
        <p class="lead text-muted mx-auto" style="max-width: 600px;">
            Dari gunung hingga lautan, setiap foto menyimpan kisah tentang dunia yang menakjubkan.
        </p>
        
        <form action="{{ route('gallery.index') }}" method="GET" class="d-flex justify-content-center mt-4 mx-auto" style="max-width: 600px;">
            <input type="text" name="search" class="form-control me-2" placeholder="Cari Inspirasimu...">
            
            <select name="category" class="form-select me-2">
                <option value="">Semua Kategori</option>
                {{-- Logic Loop Kategori --}}
                @foreach ($categories as $category)
                    @if (is_array($category)) 
                        <option value="{{ $category['id'] }}" 
                                {{ request('category') == $category['id'] ? 'selected' : '' }}>
                            {{ $category['name'] }}
                        </option>
                    @endif
                @endforeach
            </select>
            <button type="submit" class="btn btn-warning">Filter</button>
        </form>
    </div>

    <h2 class="mb-4 text-center">Karya Alam dari Lensa Mereka</h2>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mt-4">
        
        @forelse ($images as $image)
            @if (is_array($image) && isset($image['id']))
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    
                    <a href="{{ route('images.show', $image['id']) }}">
                        <img src="{{ env('SUPABASE_URL') }}/storage/v1/object/public/images/{{ $image['image_path'] }}" 
                             alt="{{ $image['title'] }}" 
                             class="card-img-top"
                             loading="lazy">
                    </a>

                    <div class="card-body">
                        <h5 class="card-title text-truncate">{{ $image['title'] }}</h5>
                        <p class="card-text text-muted mb-1"><small>📍 {{ $image['location'] ?? 'N/A' }} | 👤 {{ $image['user']['name'] ?? 'Anonim' }}</small></p>
                        
                        {{-- Aksi CRUD (Edit/Delete) hanya jika login dan pemilik --}}
                        @auth
                            @if (Auth::id() == $image['user_id'])
                            <div class="d-flex justify-content-between mt-3">
                                <a href="{{ route('images.edit', $image['id']) }}" class="btn btn-sm btn-primary">Edit</a>
                                {{-- Form Delete Daffa --}}
                                <form method="POST" action="{{ route('images.destroy', $image['id']) }}" onsubmit="return confirm('Yakin ingin menghapus karya ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </div>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
            @endif
        @empty
            <div class="col-12 text-center py-5">
                <p class="lead text-muted">Belum ada karya yang diunggah. Jadilah yang pertama!</p>
            </div>
        @endforelse
    </div>

@endsection