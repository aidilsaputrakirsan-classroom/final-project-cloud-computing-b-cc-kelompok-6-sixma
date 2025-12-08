@extends('layouts.admin-master')

@section('content')

{{-- PAGE TITLE --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Dashboard Admin</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm">System Overview</p>
    </div>
</div>

{{-- WIDGETS STATISTIK (DATA REAL) --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

    {{-- Total User --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-md dark:bg-[#020617] dark:border-[#1f2937]">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-xs uppercase tracking-wide dark:text-gray-400">Total User</p>
                {{-- 🛑 DATA REAL --}}
                <h2 class="text-3xl text-gray-900 font-bold mt-2 dark:text-white">{{ number_format($totalUsers ?? 0) }}</h2>
                {{-- 🛑 DATA REAL --}}
                <p class="text-xs mt-2 text-emerald-500 dark:text-emerald-400">↑ {{ $newUsersCount ?? 0 }} user baru bulan ini</p>
            </div>
            <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center">
                <iconify-icon icon="solar:users-group-rounded-bold-duotone" class="text-2xl text-purple-600 dark:text-purple-400"></iconify-icon>
            </div>
        </div>
    </div>

    {{-- Admin Aktif (Tetap Dummy/Hardcoded karena data real memerlukan logic Auth) --}}
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

    {{-- Total Aktivitas (Postingan) --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-md dark:bg-[#020617] dark:border-[#1f2937]">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-xs uppercase tracking-wide dark:text-gray-400">Total Postingan</p>
                {{-- 🛑 DATA REAL --}}
                <h2 class="text-3xl text-gray-900 font-bold mt-2 dark:text-white">{{ number_format($totalActivities ?? 0) }}</h2>
                <p class="text-emerald-500 text-xs mt-2 dark:text-emerald-400">Total Aktivitas Terkait</p>
            </div>
            <div class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center">
                <iconify-icon icon="solar:gallery-bold-duotone" class="text-2xl text-emerald-600 dark:text-emerald-400"></iconify-icon>
            </div>
        </div>
    </div>

    {{-- Average Time (Tetap Dummy) --}}
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

{{-- LOG AKTIVITAS USER (DATA REAL DARI REPORTS) --}}
<div class="mt-10 bg-white border border-gray-200 rounded-xl p-6 shadow-md dark:bg-[#020617] dark:border-[#1f2937]">

    <div class="flex items-center justify-between mb-4">
        <h3 class="text-gray-900 text-lg font-semibold dark:text-white">Log Aktivitas User (Reports)</h3>
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
                
                {{-- 🛑 LOOPING DATA REPORTS REAL --}}
                @forelse($activityLogs as $log)
                <tr data-type="report" class="hover:bg-gray-50/70 dark:hover:bg-[#020617]/70">
                    <td class="p-3 text-xs text-gray-400">{{ \Carbon\Carbon::parse($log['created_at'])->diffForHumans() }}</td>
                    <td class="p-3 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center text-xs font-semibold text-red-600 dark:text-red-400">{{ strtoupper(substr($log['users']['name'] ?? 'U', 0, 1)) }}</div>
                        {{ $log['users']['name'] ?? 'User Deleted' }}
                    </td>
                    <td class="p-3"><span class="bg-red-500/10 text-red-600 dark:text-red-400 px-2 py-1 rounded text-xs font-semibold">Report</span></td>
                    <td class="p-3 text-xs text-gray-600 dark:text-gray-400">{{ $log['reason'] ?? 'Konten dilaporkan' }}</td>
                    <td class="p-3 text-center text-indigo-500 text-xs">#{{ $log['image_id'] }}</td>
                    <td class="p-3 text-center">
                        {{-- MENGGUNAKAN ID ASLI DARI LOG --}}
                        <a href="{{ route('admin.post.show', ['post' => $log['image_id']]) ?? '#' }}" 
                           class="px-3 py-1.5 rounded-lg bg-indigo-500 text-white text-xs hover:bg-indigo-600 transition">
                            Lihat
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-4 text-center text-gray-500">Tidak ada aktivitas report baru saat ini.</td>
                </tr>
                @endforelse

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