@extends('layouts.admin-master')

@section('content')

{{-- PAGE TITLE --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-white">Admin Panel</h1>
        <p class="text-gray-400 text-sm">System Overview</p>
    </div>
</div>

{{-- WIDGETS --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

    {{-- Total User --}}
    <div class="bg-[#020617] border border-[#1f2937] rounded-xl p-6 shadow-lg shadow-black/30">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-400 text-xs uppercase tracking-wide">Total User</p>
                <h2 class="text-3xl text-white font-bold mt-2">{{ $totalUsers }}</h2>
                <p class="text-xs mt-2 {{ $userGrowth >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                    {{ $userGrowth >= 0 ? '↑' : '↓' }} {{ abs($userGrowth) }}% sejak bulan lalu
                </p>
            </div>

            <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center">
                <iconify-icon icon="solar:users-group-rounded-bold-duotone"
                              class="text-2xl text-purple-400"></iconify-icon>
            </div>
        </div>
    </div>

    {{-- Online Users --}}
    <div class="bg-[#020617] border border-[#1f2937] rounded-xl p-6 shadow-lg shadow-black/30">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-400 text-xs uppercase tracking-wide">User Online</p>
                <h2 class="text-3xl text-white font-bold mt-2">{{ $onlineUsers }}</h2>
                <p class="text-gray-400 text-xs mt-2">Sesi aktif: {{ $onlineUsers }}</p>
            </div>

            <div class="w-12 h-12 bg-sky-500/10 rounded-xl flex items-center justify-center">
                <iconify-icon icon="solar:shield-user-bold-duotone"
                              class="text-2xl text-sky-400"></iconify-icon>
            </div>
        </div>
    </div>

    {{-- Total Uploads --}}
    <div class="bg-[#020617] border border-[#1f2937] rounded-xl p-6 shadow-lg shadow-black/30">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-400 text-xs uppercase tracking-wide">Total Uploads</p>
                <h2 class="text-3xl text-white font-bold mt-2">{{ $totalImages }}</h2>
                <p class="text-emerald-400 text-xs mt-2">Server OK · Database Connected</p>
            </div>

            <div class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center">
                <iconify-icon icon="solar:gallery-bold-duotone"
                              class="text-2xl text-emerald-400"></iconify-icon>
            </div>
        </div>
    </div>

    {{-- Laporan Baru --}}
    <div class="bg-[#020617] border border-[#1f2937] rounded-xl p-6 shadow-lg shadow-black/30">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-400 text-xs uppercase tracking-wide">Laporan Baru</p>
                <h2 class="text-3xl text-white font-bold mt-2">{{ $pendingReportsCount }}</h2>
                <p class="text-red-400 text-xs mt-2">{{ $criticalReports }} Pending · Perlu tindakan</p>
            </div>

            <div class="w-12 h-12 bg-red-500/10 rounded-xl flex items-center justify-center">
                <iconify-icon icon="solar:bell-bing-bold-duotone"
                              class="text-2xl text-red-400"></iconify-icon>
            </div>
        </div>
    </div>

</div>

{{-- LOG AKTIVITAS --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-10">

    <div class="xl:col-span-2 bg-[#020617] border border-[#1f2937] rounded-xl p-6 shadow-lg shadow-black/30">

        <div class="flex items-center justify-between mb-4">
            <h3 class="text-white text-lg font-semibold">Log Aktivitas User</h3>
            <button class="px-3 py-1.5 rounded-lg border border-[#374151] text-gray-300 text-xs hover:bg-[#111827] transition">
                Lihat Semua
            </button>
        </div>

        <div class="space-y-3">
            @forelse ($activities as $a)
                <div class="flex gap-4 p-3 rounded-lg hover:bg-[#0b1120] transition-colors">

                    {{-- AVATAR --}}
                    <div class="w-10 h-10 rounded-full bg-[#1f2937] flex items-center justify-center text-sm font-semibold text-white">
                        {{ strtoupper(substr($a->user->name ?? 'U', 0, 1)) }}
                    </div>

                    {{-- CONTENT --}}
                    <div class="flex-1">
                        <p class="text-gray-300 text-sm">
                            <strong>{{ $a->user->name ?? 'Guest' }}</strong>
                            <span class="text-gray-500 text-xs">· {{ $a->created_at->diffForHumans() }}</span>
                        </p>

                        @if ($a->type == 'report')
                            <p class="text-red-400 text-xs font-semibold mt-1">Melaporkan</p>
                            <p class="text-gray-400 text-xs mt-1">"{{ $a->reason }}"</p>

                        @elseif ($a->type == 'comment')
                            <p class="text-sky-400 text-xs font-semibold mt-1">Komentar</p>
                            <p class="text-gray-400 text-xs mt-1">"{{ $a->content }}"</p>

                        @elseif ($a->type == 'like')
                            <p class="text-pink-400 text-xs font-semibold mt-1">Menyukai</p>
                            <p class="text-gray-400 text-xs mt-1">Postingan #{{ $a->image_id }}</p>
                        @endif
                    </div>
                </div>

            @empty
                <p class="text-gray-500 text-center py-6 text-sm">Belum ada aktivitas user.</p>
            @endforelse
        </div>

    </div>

</div>

{{-- USER BARU --}}
<div class="mt-10 bg-[#020617] border border-[#1f2937] rounded-xl p-6 shadow-lg shadow-black/30">

    <div class="flex items-center justify-between mb-4">
        <h3 class="text-white text-lg font-semibold">User Baru Terdaftar</h3>
        <button class="px-3 py-1.5 rounded-lg border border-[#374151] text-gray-300 text-xs hover:bg-[#111827] transition">
            Refresh
        </button>
    </div>

    <div class="overflow-y-auto max-h-[400px] rounded-lg border border-[#111827]">
        <table class="w-full text-left text-gray-300 text-sm">
            <thead class="bg-[#020617] sticky top-0 z-10">
                <tr class="border-b border-[#111827] text-xs uppercase tracking-wide text-gray-400">
                    <th class="p-3 font-medium">Nama</th>
                    <th class="p-3 font-medium">Email</th>
                    <th class="p-3 font-medium">Tanggal</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-[#111827]">
                @forelse ($newUsers as $u)
                    <tr class="hover:bg-[#020617]/70">
                        <td class="p-3 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-sky-500/20 flex items-center justify-center text-xs font-semibold text-sky-400">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <span>{{ $u->name }}</span>
                        </td>

                        <td class="p-3">
                            {{ $u->email }}
                        </td>

                        <td class="p-3">
                            {{ $u->created_at->format('d M Y') }}
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="3" class="text-center text-gray-500 py-6 text-sm">
                            Belum ada user terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

</div>

@endsection
