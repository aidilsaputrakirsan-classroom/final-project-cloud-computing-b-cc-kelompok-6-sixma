@extends('layouts.app')

@section('title', 'Notifikasi')

@push('styles')
<style>
    .notif-wrapper {
        max-width: 750px;
        margin: 50px auto;
        padding: 35px;
        background: rgba(20, 20, 20, 0.88);
        border-radius: 20px;
        border: 1px solid rgba(246, 199, 77, 0.25);
        box-shadow: 0 0 25px rgba(0, 0, 0, 0.55);
    }

    .notif-item {
        background: rgba(30, 30, 30, 0.95);
        border-left: 4px solid #F6C74D;
        padding: 18px;
        border-radius: 14px;
        margin-bottom: 16px;
        transition: 0.25s;
    }

    .notif-item:hover {
        background: rgba(40, 40, 40, 0.96);
        transform: translateY(-3px);
        box-shadow: 0 0 12px rgba(246, 199, 77, 0.35);
    }

    .notif-empty {
        padding: 50px 20px;
        text-align: center;
        color: #d3d3d3;
    }

    .notif-empty-icon {
        font-size: 70px;
        color: #F6C74D;
        opacity: .8;
        margin-bottom: 15px;
    }
</style>
@endpush

@section('content')

<div class="notif-wrapper">

    <h2 class="text-center mb-4" style="color:#F6C74D; font-weight:700;">
        Notifikasi
    </h2>

    {{-- Kosong --}}
    @if (empty($notifications) || count($notifications) === 0)
        <div class="notif-empty">
            <div class="notif-empty-icon">🔔</div>
            <p>Tidak ada notifikasi baru untuk saat ini.</p>
        </div>
    @else

        @foreach ($notifications as $notification)
            <div class="notif-item">
                <p class="mb-1 text-light" style="font-size:1.05rem;">
                    {{ $notification['message'] ?? 'Notifikasi baru' }}
                </p>
                <small class="text-muted">
                    {{ \Carbon\Carbon::parse($notification['created_at'] ?? now())->diffForHumans() }}
                </small>
            </div>
        @endforeach

    @endif

</div>

@endsection
