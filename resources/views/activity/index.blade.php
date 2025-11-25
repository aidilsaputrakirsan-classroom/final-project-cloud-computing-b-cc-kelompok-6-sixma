@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6">

    <h1 class="text-4xl font-bold text-white mb-10 text-center">
        Activity Log
    </h1>

    <div class="bg-white/10 backdrop-blur-lg p-6 rounded-xl shadow-lg border border-white/20">

        <table class="w-full border-collapse text-left table-fixed">
            <thead>
                <tr class="border-b border-white/20 text-gray-300 text-sm">
                    <th class="py-3 px-4 w-24">Action</th>
                    <th class="py-3 px-4 w-48">Karya</th>
                    <th class="py-3 px-4 w-32">Timestamp</th>
                </tr>
            </thead>

            <tbody class="text-white text-sm">
                @forelse ($logs as $log)
                <tr class="border-b border-white/10 hover:bg-white/5 transition">

                    {{-- ACTION --}}
                    <td class="py-3 px-4">
                        <span class="px-3 py-1 rounded-full text-xs bg-blue-600">
                            {{ strtoupper($log['action_type']) }}
                        </span>
                    </td>

                    {{-- Karya --}}
                    <td class="py-3 px-4 w-48">
                        <div class="font-semibold mb-2">{{ $log['resource_title'] }}</div>

                        @if(isset($log['details']['image_path']))
                        <div class="w-32 h-20 overflow-hidden rounded-lg border border-white/20">
                            <img src="{{ env('SUPABASE_URL') }}/storage/v1/object/public/images/{{ $log['details']['image_path'] }}"
                                 class="w-100 h-100 object-cover">
                        </div>
                        @endif
                    </td>


                    {{-- Timestamp --}}
                    <td class="py-3 px-4 text-gray-300">
                        {{ \Carbon\Carbon::parse($log['timestamp'])->format('d M Y H:i') }}
                        <div class="text-xs text-gray-400">
                            ({{ \Carbon\Carbon::parse($log['timestamp'])->diffForHumans() }})
                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-6 text-gray-400">
                        Belum ada aktivitas.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</div>
@endsection
