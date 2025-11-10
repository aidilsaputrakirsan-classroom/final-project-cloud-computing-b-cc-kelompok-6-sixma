<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upload Gambar Baru</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    /* ======== LATAR & FONT ======== */
    body {
      background: radial-gradient(circle at top, #1a1a1a, #0A0A0A);
      color: #f8f9fa;
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 30px;
    }

    /* ======== CARD FORM ======== */
    .upload-card {
      width: 100%;
      max-width: 600px;
      background: rgba(20, 20, 20, 0.85);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(246, 199, 77, 0.2);
      border-radius: 25px;
      box-shadow: 0 0 40px rgba(246, 199, 77, 0.15);
      padding: 40px;
      transition: all 0.4s ease;
    }
    .upload-card:hover {
      box-shadow: 0 0 50px rgba(246, 199, 77, 0.3);
      transform: translateY(-4px);
    }

    /* ======== JUDUL ======== */
    h2 {
      color: #F6C74D;
      font-weight: 700;
      text-align: center;
      margin-bottom: 30px;
      letter-spacing: 1px;
    }

    /* ======== LABEL ======== */
    label {
      color: #F6C74D;
      font-weight: 600;
      margin-bottom: 6px;
    }

    /* ======== INPUT FIELD ======== */
    .form-control {
      background: rgba(30, 30, 30, 0.95);
      color: #ffffff; /* teks input putih */
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 10px;
      padding: 10px 14px;
      transition: all 0.3s ease;
      font-weight: 500;
    }

    .form-control::placeholder {
      color: #c9a94a; /* placeholder keemasan */
      opacity: 0.8;
    }

    .form-control:focus {
      border-color: #F6C74D;
      box-shadow: 0 0 12px rgba(246, 199, 77, 0.45);
      background-color: rgba(40, 40, 40, 0.95);
      color: #fff;
    }

    /* ======== BUTTONS ======== */
    .btn-success {
      background-color: #F6C74D;
      border: none;
      color: #0A0A0A;
      font-weight: 700;
      border-radius: 12px;
      transition: all 0.3s ease;
      box-shadow: 0 0 10px rgba(246, 199, 77, 0.4);
    }
    .btn-success:hover {
      background-color: #FFD85C;
      box-shadow: 0 0 25px rgba(246, 199, 77, 0.6);
    }

    .btn-secondary {
      background-color: rgba(255, 255, 255, 0.1);
      border: none;
      color: #f8f9fa;
      font-weight: 600;
      border-radius: 12px;
      transition: all 0.3s ease;
    }
    .btn-secondary:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }

    /* ======== PREVIEW GAMBAR ======== */
    .image-preview {
      display: none;
      margin-top: 15px;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 0 15px rgba(246, 199, 77, 0.3);
      animation: fadeIn 0.4s ease-in-out;
    }

    .image-preview img {
      width: 100%;
      height: auto;
      display: block;
      border-radius: 12px;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.98); }
      to { opacity: 1; transform: scale(1); }
    }

    /* ======== RESPONSIVE ======== */
    @media (max-width: 576px) {
      .upload-card {
        padding: 25px;
        border-radius: 15px;
      }
    }
  </style>
</head>
<body>

<div class="upload-card">
  <h2>Upload Gambar Baru</h2>

  <form action="{{ route('images.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mb-3">
      <label for="title" class="form-label">Judul Gambar</label>
      <input type="text" name="title" id="title" class="form-control" placeholder="Masukkan judul gambar" required>
    </div>

    <div class="mb-3">
      <label for="image" class="form-label">File Gambar</label>
      <input type="file" name="image" id="image" class="form-control" accept="image/*" required onchange="previewImage(event)">
      <div class="image-preview" id="previewContainer">
        <img id="previewImage" alt="Preview Gambar">
      </div>
    </div>

    <div class="mb-4">
      <label for="category" class="form-label">Kategori</label>
      <input type="text" name="category" id="category" class="form-control" placeholder="Contoh: Nature, Urban, Portrait, dll">
    </div>

    <div class="text-center">
      <button type="submit" class="btn btn-success px-4 me-2">Upload</button>
      <a href="{{ route('gallery.index') }}" class="btn btn-secondary px-4">Kembali</a>
    </div>
  </form>
</div>

<script>
  function previewImage(event) {
    const file = event.target.files[0];
    const previewContainer = document.getElementById('previewContainer');
    const previewImage = document.getElementById('previewImage');

    if (file) {
      previewImage.src = URL.createObjectURL(file);
      previewContainer.style.display = 'block';
    } else {
      previewContainer.style.display = 'none';
    }
  }
</script>

</body>
</html>
