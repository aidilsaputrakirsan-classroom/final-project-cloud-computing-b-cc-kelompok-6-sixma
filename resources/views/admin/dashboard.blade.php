@extends('layouts.admin-master')

@section('content')

{{-- PAGE TITLE --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Dashboard Admin</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm">System Overview</p>
    </div>
</div>

{{-- WIDGETS STATISTIK --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

    {{-- Total User --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-md dark:bg-[#020617] dark:border-[#1f2937]">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-xs uppercase tracking-wide dark:text-gray-400">Total User</p>
                <h2 class="text-3xl text-gray-900 font-bold mt-2 dark:text-white">12,450</h2>
                <p class="text-xs mt-2 text-emerald-500 dark:text-emerald-400">↑ 3.2% sejak bulan lalu</p>
            </div>
            <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center">
                <iconify-icon icon="solar:users-group-rounded-bold-duotone" class="text-2xl text-purple-600 dark:text-purple-400"></iconify-icon>
            </div>
        </div>
    </div>

    {{-- Admin Aktif --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-md dark:bg-[#020617] dark:border-[#1f2937]">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-xs uppercase tracking-wide dark:text-gray-400">Admin Aktif</p>
                <h2 class="text-3xl text-gray-900 font-bold mt-2 dark:text-white">18</h2>
                <p class="text-gray-500 text-xs mt-2 dark:text-gray-400">Online saat ini: 4</p>
            </div>
            <div class="w-12 h-12 bg-blue-500/10 rounded-xl flex items-center justify-center">
                <iconify-icon icon="solar:shield-user-bold-duotone" class="text-2xl text-blue-600 dark:text-blue-400"></iconify-icon>
            </div>
        </div>
    </div>

    {{-- Total Aktivitas --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-md dark:bg-[#020617] dark:border-[#1f2937]">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-xs uppercase tracking-wide dark:text-gray-400">Total Aktivitas</p>
                <h2 class="text-3xl text-gray-900 font-bold mt-2 dark:text-white">8,342</h2>
                <p class="text-emerald-500 text-xs mt-2 dark:text-emerald-400">Hari ini: 142 aktivitas</p>
            </div>
            <div class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center">
                <iconify-icon icon="solar:gallery-bold-duotone" class="text-2xl text-emerald-600 dark:text-emerald-400"></iconify-icon>
            </div>
        </div>
    </div>

    {{-- Average Time --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-md dark:bg-[#020617] dark:border-[#1f2937]">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-xs uppercase tracking-wide dark:text-gray-400">Average Time</p>
                <h2 class="text-3xl text-gray-900 font-bold mt-2 dark:text-white">2.5 jam</h2>
                <p class="text-orange-500 text-xs mt-2 dark:text-orange-400">Waktu respons admin</p>
            </div>
            <div class="w-12 h-12 bg-orange-500/10 rounded-xl flex items-center justify-center">
                <iconify-icon icon="solar:clock-square-bold-duotone" class="text-2xl text-orange-600 dark:text-orange-400"></iconify-icon>
            </div>
        </div>
    </div>
</div>

{{-- LOG AKTIVITAS USER --}}
<div class="mt-10 bg-white border border-gray-200 rounded-xl p-6 shadow-md dark:bg-[#020617] dark:border-[#1f2937]">

    <div class="flex items-center justify-between mb-4">
        <h3 class="text-gray-900 text-lg font-semibold dark:text-white">Log Aktivitas User</h3>
        <div id="activity-filters" class="space-x-2">
            {{-- Tombol Filter --}}
            <button data-filter="all" class="filter-btn px-3 py-1.5 rounded-lg border border-gray-300 text-gray-600 text-xs hover:bg-gray-50 dark:border-[#374151] dark:text-gray-300 dark:hover:bg-[#111827] bg-gray-100 dark:bg-[#111827]">
                Semua
            </button>
            <button data-filter="report" class="filter-btn px-3 py-1.5 rounded-lg border border-red-500/30 text-red-600 text-xs bg-red-500/10 dark:text-red-400 dark:border-red-400/30">
                Report
            </button>
            <button data-filter="comment" class="filter-btn px-3 py-1.5 rounded-lg border border-sky-500/30 text-sky-600 text-xs bg-sky-500/10 dark:text-sky-400 dark:border-sky-400/30">
                Comment
            </button>
            <button data-filter="like" class="filter-btn px-3 py-1.5 rounded-lg border border-pink-500/30 text-pink-600 text-xs bg-pink-500/10 dark:text-pink-400 dark:border-pink-400/30">
                Like
            </button>
        </div>
    </div>

    {{-- KONTEN TABEL --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-[#111827]">
        <table class="w-full text-left text-gray-700 text-sm dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-[#020617]">
                <tr class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500 dark:border-[#111827] dark:text-gray-400">
                    <th class="p-3 font-medium">Waktu</th>
                    <th class="p-3 font-medium">User</th>
                    <th class="p-3 font-medium">Tipe</th>
                    <th class="p-3 font-medium w-96">Detail Aktivitas</th>
                    <th class="p-3 font-medium text-center">Postingan</th>
                    <th class="p-3 font-medium text-center">Aksi</th>
                </tr>
            </thead>
            
            <tbody id="activity-list" class="divide-y divide-gray-100 dark:divide-[#111827]">
                {{-- ITEM 1: Report (Dummy Data) --}}
                <tr data-type="report" class="hover:bg-gray-50/70 dark:hover:bg-[#020617]/70">
                    <td class="p-3 text-xs text-gray-400">2 menit lalu</td>
                    <td class="p-3 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center text-xs font-semibold text-red-600 dark:text-red-400">AR</div>
                        Ahmad Rizki
                    </td>
                    <td class="p-3"><span class="bg-red-500/10 text-red-600 dark:text-red-400 px-2 py-1 rounded text-xs font-semibold">Report</span></td>
                    <td class="p-3 text-xs text-gray-600 dark:text-gray-400">Konten Tidak Pantas: "Postingan ini melanggar..."</td>
                    <td class="p-3 text-center text-indigo-500 text-xs">#2451</td>
                    <td class="p-3 text-center">
                        <button class="px-3 py-1.5 rounded-lg bg-indigo-500 text-white text-xs hover:bg-indigo-600 transition">Lihat</button>
                    </td>
                </tr>

                {{-- ITEM 2: Comment (Dummy Data) --}}
                <tr data-type="comment" class="hover:bg-gray-50/70 dark:hover:bg-[#020617]/70">
                    <td class="p-3 text-xs text-gray-400">5 menit lalu</td>
                    <td class="p-3 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-sky-500/20 flex items-center justify-center text-xs font-semibold text-sky-600 dark:text-sky-400">SN</div>
                        Siti Nurhaliza
                    </td>
                    <td class="p-3"><span class="bg-sky-500/10 text-sky-600 dark:text-sky-400 px-2 py-1 rounded text-xs font-semibold">Comment</span></td>
                    <td class="p-3 text-xs text-gray-600 dark:text-gray-400">"Karya yang sangat menginspirasi! Saya suka..."</td>
                    <td class="p-3 text-center text-indigo-500 text-xs">#2448</td>
                    <td class="p-3 text-center">
                        <button class="px-3 py-1.5 rounded-lg bg-indigo-500 text-white text-xs hover:bg-indigo-600 transition">Lihat</button>
                    </td>
                </tr>
                
                {{-- ITEM 3: Like (Dummy Data) --}}
                <tr data-type="like" class="hover:bg-gray-50/70 dark:hover:bg-[#020617]/70">
                    <td class="p-3 text-xs text-gray-400">8 menit lalu</td>
                    <td class="p-3 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-pink-500/20 flex items-center justify-center text-xs font-semibold text-pink-600 dark:text-pink-400">BS</div>
                        Budi Santoso
                    </td>
                    <td class="p-3"><span class="bg-pink-500/10 text-pink-600 dark:text-pink-400 px-2 py-1 rounded text-xs font-semibold">Like</span></td>
                    <td class="p-3 text-xs text-gray-600 dark:text-gray-400">Menyukai postingan Anda</td>
                    <td class="p-3 text-center text-indigo-500 text-xs">#2448</td>
                    <td class="p-3 text-center">
                        <button class="px-3 py-1.5 rounded-lg bg-indigo-500 text-white text-xs hover:bg-indigo-600 transition">Lihat</button>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

    <div class="text-center mt-6">
        <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700 transition">
            Load More Activity
        </button>
    </div>

</div>

@endsection