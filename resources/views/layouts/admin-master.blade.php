<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.partials.title-meta', ['title' => $title ?? 'Admin Panel'])

    {{-- 🛑 KEMBALIKAN KE @vite STANDAR (Setelah NPM Bersih) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- SCRIPT ICONIFY (Diletakkan di head agar ikon dimuat cepat) --}}
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>

</head>

{{-- BODY: Pastikan Dark Mode ter-apply --}}
<body class="dark:bg-[#0f172a] dark:text-gray-200">

    <div class="flex min-h-screen">

        {{-- SIDEBAR --}}
        @include('layouts.admin.sidebar')

        {{-- MAIN WRAPPER (Tempat konten dimuat) --}}
        <div id="main-wrapper"
             class="flex flex-col flex-1 ml-64 transition-all duration-300 min-h-screen"> 

            {{-- TOPBAR --}}
            @include('layouts.admin.topbar')

            {{-- CONTENT AREA: KUNCI UTAMA LAYOUT --}}
            <main class="p-6 flex-1"> 
                @yield('content') {{-- 🚨 INI PENTING --}}
            </main>

            {{-- FOOTER --}}
            @include('layouts.admin.footer')
        </div>

    </div>
    
    {{-- SCRIPTS: FILTER AKTIVITAS USER --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filters = document.querySelectorAll('#activity-filters .filter-btn');
            const activityList = document.getElementById('activity-list');

            if (filters.length && activityList) {
                filters.forEach(button => {
                    button.addEventListener('click', () => {
                        const filterType = button.getAttribute('data-filter');
                        
                        filters.forEach(btn => btn.classList.remove('bg-gray-100', 'dark:bg-[#111827]'));
                        button.classList.add('bg-gray-100', 'dark:bg-[#111827]');
                        
                        activityList.querySelectorAll('tr').forEach(row => {
                            const rowType = row.getAttribute('data-type');
                            
                            if (filterType === 'all' || rowType === filterType) {
                                row.style.display = ''; 
                            } else {
                                row.style.display = 'none'; 
                            }
                        });
                    });
                });
                document.querySelector('[data-filter="all"]').click();
            }
        });
    </script>

</body>
</html>