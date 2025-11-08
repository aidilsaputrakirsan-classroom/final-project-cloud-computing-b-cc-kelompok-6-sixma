<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artrium | @yield('title', 'Galeri')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- @vite('resources/css/app.css') --}} 

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="/">🌿 Artrium</a>
        </div>
    </nav>

    <main>
        @yield('content') 
    </main>

    <footer class="text-center mt-5 py-3 text-muted">
        <small>© 2025 Artrium Project — Balikpapan</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>