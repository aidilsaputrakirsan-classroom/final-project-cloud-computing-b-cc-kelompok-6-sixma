@extends('layouts.app')

@section('content')
<style>
    body {
        background-color: #0A0A0A;
        font-family: 'Poppins', sans-serif;
        color: #fff;
    }

    .upload-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 40px 15px;
    }

    .upload-card {
        background-color: #111;
        border-radius: 20px;
        padding: 45px;
        max-width: 650px;
        width: 100%;
        box-shadow: 0 0 25px rgba(246, 199, 77, 0.15);
        border: 1px solid rgba(246, 199, 77, 0.3);
        transition: all 0.3s ease;
    }

    .upload-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0 40px rgba(246, 199, 77, 0.25);
    }

    .upload-card h2 {
        text-align: center;
        font-weight: 700;
        font-size: 1.8rem;
        color: #F6C74D;
        margin-bottom: 15px;
        letter-spacing: 1px;
    }

    .divider {
        width: 60px;
        height: 3px;
        background: #F6C74D;
        margin: 0 auto 30px auto;
        border-radius: 2px;
    }

    .form-label {
        color: #F6C74D;
        font-weight: 500;
        margin-bottom: 6px;
    }

    .form-control,
    .custom-select {
        background-color: #0A0A0A;
        border: 1px solid rgba(246, 199, 77, 0.4);
        border-radius: 10px;
        color: #fff;
        padding: 10px 14px;
        transition: all 0.3s ease;
        width: 100%;
        appearance: none;
        cursor: pointer;
    }

    .form-control:focus,
    .custom-select:focus {
        border-color: #F6C74D;
        box-shadow: 0 0 10px rgba(246, 199, 77, 0.4);
        outline: none;
    }

    /* Custom Dropdown Arrow */
    .select-wrapper {
        position: relative;
    }

    .select-wrapper::after {
        content: "▼";
        font-size: 0.9rem;
        color: #F6C74D;
        position: absolute;
        top: 50%;
        right: 15px;
        transform: translateY(-50%);
        pointer-events: none;
    }

    input[type="file"]::file-selector-button {
        background-color: #F6C74D;
        border: none;
        color: #0A0A0A;
        border-radius: 8px;
        padding: 6px 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s;
    }

    input[type="file"]::file-selector-button:hover {
        background-color: #dbae3c;
    }

    .btn-upload {
        width: 100%;
        background-color: #F6C74D;
        border: none;
        color: #0A0A0A;
        font-weight: 700;
        text-transform: uppercase;
        border-radius: 12px;
        padding: 14px;
        letter-spacing: 1px;
        margin-top: 10px;
        transition: all 0.3s ease;
    }

    .btn-upload:hover {
        background-color: #e6b840;
        transform: scale(1.03);
        box-shadow: 0 5px 20px rgba(246, 199, 77, 0.35);
    }

    .alert {
        border-radius: 10px;
        font-size: 0.9rem;
        padding: 10px 15px;
    }

    .alert-danger {
        background-color: rgba(255, 0, 0, 0.15);
        border: 1px solid #ff4d4d;
        color: #ffcccc;
    }

    .alert-success {
        background-color: rgba(246, 199, 77, 0.2);
        border: 1px solid #F6C74D;
        color: #fff4cc;
    }
</style>

<div class="upload-wrapper">
    <div class="upload-card">
        <h2>📸 Unggah Karya Fotografi Alam</h2>
        <div class="divider"></div>

        {{-- 🔴 Error Messages --}}
        @if ($errors->any())
            <div class="alert alert-danger mb-3">
                <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 🟢 Success Message --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @elseif (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('images.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Judul --}}
            <div class="mb-3">
                <label for="title" class="form-label">Judul</label>
                <input type="text" name="title" id="title" class="form-control"
                       placeholder="Contoh: Sunset di Pantai Lamaru" required>
            </div>

            {{-- Deskripsi --}}
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea name="description" id="description" class="form-control" rows="3"
                          placeholder="Ceritakan sedikit tentang foto ini..."></textarea>
            </div>

            {{-- Dropdown Kategori --}}
            <div class="mb-3">
                <label for="category_id" class="form-label">Kategori</label>
                <div class="select-wrapper">
                    <select name="category_id" id="category_id" class="custom-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        @if (is_array($categories))
                            @foreach ($categories as $category)
                                @if (is_array($category) && isset($category['id'], $category['name']))
                                    <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                @endif
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            {{-- Lokasi --}}
            <div class="mb-3">
                <label for="location" class="form-label">Lokasi Pengambilan</label>
                <input type="text" name="location" id="location" class="form-control"
                       placeholder="Contoh: Balikpapan, Kalimantan Timur">
            </div>

            {{-- File Gambar --}}
            <div class="mb-4">
                <label for="image" class="form-label">File Gambar</label>
                <input type="file" name="image" id="image" class="form-control" accept="image/*" required>
            </div>

            <button type="submit" class="btn-upload">Unggah Sekarang</button>
        </form>
    </div>
</div>
@endsection
