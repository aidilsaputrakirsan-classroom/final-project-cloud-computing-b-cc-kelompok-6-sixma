@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-10 text-white">

        <h2 class="text-3xl font-bold mb-6">Notifikasi</h2>

        @if (empty($notifications) || count($notifications) === 0)
            <p class="text-gray-400">Belum ada notifikasi.</p>
        @endif

        @foreach ($notifications as $notif)
            <div class="border border-gray-700 rounded-lg p-4 mb-4 bg-black bg-opacity-40">
                <p class="text-lg">{{ $notif['message'] }}</p>
                <p class="text-sm text-gray-400 mt-1">
                    {{ \Carbon\Carbon::parse($notif['created_at'])->diffForHumans() }}
                </p>
            </div>
        @endforeach

    </div>
@endsection
