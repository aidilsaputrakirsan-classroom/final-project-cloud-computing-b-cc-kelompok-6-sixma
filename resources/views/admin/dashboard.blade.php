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
    {{-- kartu statistik kamu di sini: totalUsers, adminAktif, totalActivities, dll --}}
</div>

{{-- LOG AKTIVITAS USER (15 TERAKHIR + FILTER) --}}
<div class="mt-10 bg-white border border-gray-200 rounded-xl p-6 shadow-md dark:bg-[#020617] dark:border-[#1f2937] w-full">

    <div class="flex items-center justify-between mb-4">
        <h3 class="text-gray-900 text-lg font-semibold dark:text-white">
            Log Aktivitas User (15 Terakhir)
        </h3>

        {{-- Filter button --}}
        @php
            $currentFilter = $filterType ?? 'all';
            $filterLabels = [
                'all' => 'Semua',
                'report' => 'Report',
                'comment' => 'Comment',
                'like' => 'Like',
            ];
        @endphp

        <div class="flex gap-2">
            @foreach ($filterLabels as $key => $label)
                <a href="{{ route('admin.dashboard', ['type' => $key]) }}"
                   class="px-3 py-1.5 rounded-full text-xs font-semibold border
                   {{ $currentFilter === $key
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if(count($activityLogs) > 0)
        {{-- tarik konten sampai ke kiri-kanan supaya tidak ada ruang kosong --}}
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="min-w-full text-left text-sm text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-[#020617]">
                    <tr class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500 dark:border-[#111827] dark:text-gray-400">
                        <th class="p-3">Waktu</th>
                        <th class="p-3">User</th>
                        <th class="p-3">Tipe</th>
                        <th class="p-3">Detail Aktivitas</th>
                        <th class="p-3">Postingan</th>
                        <th class="p-3">Info</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#111827]">
                    @foreach($activityLogs as $log)
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-[#020617]/70">
                            <td class="p-3 text-xs text-gray-400">
                                {{ $log['waktu'] }}
                            </td>
                            <td class="p-3">
                                <div class="font-semibold text-sm">
                                    {{ $log['user_name'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $log['user_email'] }}
                                </div>
                            </td>
                            <td class="p-3">
                                @if($log['tipe'] === 'Upload')
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-blue-500/10 text-blue-600">
                                        📤 Upload
                                    </span>
                                @elseif($log['tipe'] === 'Comment')
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-sky-500/10 text-sky-600">
                                        💬 Comment
                                    </span>
                                @elseif($log['tipe'] === 'Like')
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-emerald-500/10 text-emerald-600">
                                        ❤️ Like
                                    </span>
                                @elseif($log['tipe'] === 'Report')
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-red-500/10 text-red-600">
                                        ⚠️ Report
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 text-xs text-gray-600 dark:text-gray-400">
                                {{ $log['detail'] }}
                            </td>
                            <td class="p-3">
                                @if($log['postingan_url'])
                                    <img src="{{ $log['postingan_url'] }}"
                                         alt="Post"
                                         class="w-14 h-14 object-cover rounded-lg mb-1">
                                @endif
                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                    {{ $log['postingan_title'] }}
                                </div>
                            </td>
                            <td class="p-3">
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $log['category'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-center text-gray-500 text-sm dark:text-gray-400">
            Tidak ada aktivitas saat ini.
        </p>
    @endif

    <form action="{{ route('admin.dashboard.clear-cache') }}" method="POST" class="mt-4 text-right">
        @csrf
        <button type="submit"
                class="inline-flex items-center px-3 py-2 rounded-lg bg-yellow-500 text-white text-xs font-semibold hover:bg-yellow-600 transition">
            🔄 Refresh Data
        </button>
    </form>
</div>

@endsection
