{{-- resources/views/components/activity-badge.blade.php --}}
@props(['type'])

@php
    // normalisasi tipe agar case-insensitive
    $t = strtolower($type ?? 'unknown');
@endphp

@if($t === 'upload')
    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">
        <span class="mr-1">📤</span> Upload
    </span>
@elseif($t === 'comment')
    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 dark:bg-sky-900/20 dark:text-sky-300">
        <span class="mr-1">💬</span> Comment
    </span>
@elseif($t === 'like')
    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300">
        <span class="mr-1">❤️</span> Like
    </span>
@elseif($t === 'report')
    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300">
        <span class="mr-1">⚠️</span> Report
    </span>
@else
    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-[#111827] dark:text-gray-300">
        {{ ucfirst($t) }}
    </span>
@endif
