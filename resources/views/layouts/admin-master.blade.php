<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.partials.title-meta', ['title' => $title ?? 'Admin Panel'])
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- BODY: buat full dark --}}
<body class="bg-[#0f172a] text-gray-200">

    <div class="flex min-h-screen bg-[#0f172a]">

        {{-- SIDEBAR --}}
        @include('layouts.admin.sidebar')

        {{-- MAIN WRAPPER --}}
        <div id="main-wrapper"
             class="flex flex-col flex-1 ml-64 transition-all duration-300 bg-[#0f172a] min-h-screen">

            {{-- TOPBAR --}}
            @include('layouts.admin.topbar')

            {{-- CONTENT --}}
            <main class="p-6 bg-[#0f172a]">
                @yield('content')
            </main>

            {{-- FOOTER --}}
            @include('layouts.admin.footer')
        </div>

    </div>

    {{-- SIDEBAR TOGGLE SCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const sidebar   = document.getElementById('admin-sidebar');
            const wrapper   = document.getElementById('main-wrapper');
            const toggleBtn = document.getElementById('sidebar-toggle');
            const labels    = document.querySelectorAll('.sidebar-label');
            const sections  = document.querySelectorAll('.sidebar-section-title');

            if (!sidebar || !wrapper || !toggleBtn) return;

            let collapsed = false;

            toggleBtn.addEventListener('click', function () {
                collapsed = !collapsed;

                if (collapsed) {
                    // Collapsed width
                    sidebar.classList.remove('w-64');
                    sidebar.classList.add('w-20');

                    wrapper.classList.remove('ml-64');
                    wrapper.classList.add('ml-20');

                    // Hide text labels
                    labels.forEach(el => el.classList.add('hidden'));
                    sections.forEach(el => el.classList.add('hidden'));

                } else {
                    // Expanded width
                    sidebar.classList.remove('w-20');
                    sidebar.classList.add('w-64');

                    wrapper.classList.remove('ml-20');
                    wrapper.classList.add('ml-64');

                    // Show text labels
                    labels.forEach(el => el.classList.remove('hidden'));
                    sections.forEach(el => el.classList.remove('hidden'));
                }
            });
        });
    </script>

</body>
</html>
