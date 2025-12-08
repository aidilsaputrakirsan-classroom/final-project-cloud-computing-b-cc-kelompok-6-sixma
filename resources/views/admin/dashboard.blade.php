@extends('layouts.admin-master')

@section('content')

<div class="pt-4 pb-2">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white tracking-tight">Dashboard Admin</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">System Overview</p>
</div>

<!-- STATISTICS -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4 mb-8">

    {{-- Card --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-md p-5 flex flex-col justify-between hover:shadow-lg transition">
        <div class="flex justify-between items-center">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Users</span>
            <span class="text-indigo-500 text-lg">👥</span>
        </div>
        <div>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $totalUsers }}</p>
            <p class="text-xs text-green-600 mt-1">+{{ $newUsersCount }} pengguna bulan lalu</p>
        </div>
    </div>

    {{-- Card --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-md p-5 flex flex-col justify-between hover:shadow-lg transition">
        <div class="flex justify-between items-center">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Aktivitas</span>
            <span class="text-emerald-500 text-lg">📊</span>
        </div>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $totalActivities }}</p>
        <p class="text-xs text-gray-500">Total semua interaksi</p>
    </div>

    {{-- Card --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-md p-5 flex flex-col justify-between hover:shadow-lg transition">
        <div class="flex justify-between items-center">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Admin Aktif</span>
            <span class="text-red-500 text-lg">🧩</span>
        </div>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $adminAktif }}</p>
        <p class="text-xs text-gray-500">Online saat ini (Asumsi)</p>
    </div>
</div>


<!-- ACTIVITY LOG -->
<div class="bg-white dark:bg-[#0f172a] border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg p-6">

    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Log Aktivitas User <span class="text-gray-500 font-medium">(15 Terakhir)</span>
        </h3>

        {{-- FILTER --}}
        <div class="flex gap-2 bg-gray-100 dark:bg-gray-800 p-1 rounded-full">
            @foreach (['all'=>'Semua','upload'=>'Upload','report'=>'Report','comment'=>'Comment','like'=>'Like'] as $key => $label)
                <a href="{{ route('admin.dashboard', ['type' => $key]) }}"
                   class="px-4 py-1.5 rounded-full text-sm font-medium transition 
                          {{ ($filterType ?? 'all') === $key 
                                ? 'bg-white dark:bg-gray-700 shadow text-indigo-600 dark:text-indigo-400' 
                                : 'text-gray-600 dark:text-gray-300 hover:text-indigo-500' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 dark:bg-gray-800 text-xs uppercase tracking-wide text-gray-600 dark:text-gray-300">
                <tr>
                    <th class="p-3">Waktu</th>
                    <th class="p-3">User</th>
                    <th class="p-3">Tipe</th>
                    <th class="p-3 w-64">Detail Aktivitas</th>
                    <th class="p-3 text-center">Postingan</th>
                    <th class="p-3">Info</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($activityLogs as $log)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">

                    {{-- WAKTU --}}
                    <td class="p-3 text-gray-500 dark:text-gray-400 text-xs">
                        {{ $log['waktu'] ?? '-' }}
                    </td>

                    {{-- USER --}}
                    <td class="p-3">
                        <div class="font-semibold text-gray-900 dark:text-white text-sm">{{ $log['user_name'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $log['user_email'] }}</div>
                    </td>

                    {{-- TIPE --}}
                    <td class="p-3">
                        @include('components.activity-badge', ['type' => $log['tipe']])
                    </td>

                    {{-- DETAIL --}}
                    <td class="p-3 text-gray-700 dark:text-gray-300">
                        {{ $log['detail'] }}
                    </td>

                    {{-- POST --}}
                    <td class="p-3 text-center">
                        @if(!empty($log['postingan_url']))
                            <img src="{{ $log['postingan_url'] }}"
                                class="w-14 h-14 rounded-lg object-cover mx-auto border border-gray-200 dark:border-gray-700 shadow-sm">
                            <div class="text-xs text-gray-500 mt-1">{{ $log['postingan_title'] }}</div>
                        @endif
                    </td>

                    {{-- INFO --}}
                    <td class="p-3 text-gray-600 dark:text-gray-400 text-xs">
                        {{ $log['category'] }}
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>

        @if(count($activityLogs) == 0)
            <p class="p-4 text-center text-gray-500 dark:text-gray-400 text-sm">Tidak ada aktivitas.</p>
        @endif
    </div>

    {{-- REFRESH BUTTON --}}
    <form action="{{ route('admin.dashboard.clear-cache') }}" method="POST" class="mt-6 text-right">
        @csrf
        <button class="px-4 py-2 bg-yellow-500 text-white text-sm font-semibold rounded-lg shadow hover:bg-yellow-600 transition">
            🔄 Refresh Data
        </button>
    </form>

</div>

@endsection
