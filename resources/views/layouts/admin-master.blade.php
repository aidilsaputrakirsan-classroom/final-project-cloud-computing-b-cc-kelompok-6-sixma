<!DOCTYPE html>
<html lang="id">

<head>
    @include('layouts.partials.title-meta', ['title' => $title ?? 'Admin Panel'])

    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Iconify --}}
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
</head>

<body class="dark:bg-[#0f172a] dark:text-gray-200">

    <div class="flex min-h-screen">

        {{-- SIDEBAR --}}
        @include('layouts.admin.sidebar')

        {{-- MAIN WRAPPER --}}
        <div id="main-wrapper" class="flex flex-col flex-1 ml-64 transition-all duration-300 min-h-screen">

            {{-- TOPBAR --}}
            @include('layouts.admin.topbar')

            {{-- CONTENT --}}
            <main class="px-6 pb-6 flex-1">
                @yield('content')
            </main>

            {{-- FOOTER --}}
            @include('layouts.admin.footer')
        </div>

    </div>

    {{-- FILTER SCRIPT --}}
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
                            row.style.display = (filterType === 'all' || rowType === filterType)
                                ? ''
                                : 'none';
                        });
                    });
                });
            }
        });
    </script>

</body>
</html>
