<!DOCTYPE html>
<html lang="id" @yield('html-attribute')>

<head>
    {{-- Kita gunakan meta & css bawaan template agar style tidak rusak --}}
    @include('layouts.partials.title-meta', ['title' => $title ?? 'Admin Panel'])
    @include('layouts.partials.head-css')
</head>

<body>

    <div class="app-wrapper">

        {{-- Panggil Sidebar Khusus Admin --}}
        @include('layouts.admin.sidebar')

        {{-- Panggil Topbar Khusus Admin --}}
        @include('layouts.admin.topbar')

        <div class="page-content">
            <div class="container-fluid">
                
                {{-- Area Konten Berubah-ubah --}}
                @yield('content')

            </div>

            {{-- Panggil Footer Khusus Admin --}}
            @include('layouts.admin.footer')
        </div>

    </div>

    {{-- Script bawaan template --}}
    @include('layouts.partials.vendor-scripts')
    
    {{-- Slot untuk script tambahan per halaman --}}
    @yield('scripts')

</body>
</html>