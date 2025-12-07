@extends('layouts.admin-master', ['subtitle' => 'Admin Dashboard'])

@section('content')

{{-- Judul Halaman --}}
@include('layouts.partials/page-title', ['title' => 'Admin Panel', 'subtitle' => 'System Overview'])

<div class="row">
    {{-- WIDGET 1: TOTAL USER --}}
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p class="text-muted mb-0 text-truncate">Total User</p>
                        <h3 class="text-dark mt-2 mb-0">{{ number_format($totalUsers) }}</h3>
                    </div>
                    <div class="col-6">
                        <div class="ms-auto avatar-md bg-soft-primary rounded">
                            <iconify-icon icon="solar:users-group-rounded-bold" class="fs-32 avatar-title text-primary"></iconify-icon>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="{{ $userGrowth >= 0 ? 'text-success' : 'text-danger' }} me-1">
                        <i class="bx {{ $userGrowth >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }}"></i> 
                        {{ number_format(abs($userGrowth), 1) }}%
                    </span>
                    <span class="text-muted">Sejak bulan lalu</span>
                </div>
            </div>
        </div>
    </div>

    {{-- WIDGET 2: ONLINE USERS --}}
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p class="text-muted mb-0 text-truncate">User Online</p>
                        <h3 class="text-dark mt-2 mb-0">{{ $onlineUsers }}</h3>
                    </div>
                    <div class="col-6">
                        <div class="ms-auto avatar-md bg-soft-info rounded">
                            <iconify-icon icon="solar:shield-user-bold" class="fs-32 avatar-title text-info"></iconify-icon>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted">Sesi Aktif: </span>
                    <span class="text-dark fw-bold">{{ $onlineUsers }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- WIDGET 3: KESEHATAN SERVER (Diganti Total Upload) --}}
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p class="text-muted mb-0 text-truncate">Total Uploads</p>
                        <h3 class="text-dark mt-2 mb-0">{{ $totalImages }}</h3>
                    </div>
                    <div class="col-6">
                        <div class="ms-auto avatar-md bg-soft-success rounded">
                            <iconify-icon icon="solar:gallery-bold" class="fs-32 avatar-title text-success"></iconify-icon>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                     {{-- Placeholder statis untuk kesehatan server karena butuh tool khusus --}}
                    <span class="text-success me-1">Server OK</span>
                    <span class="text-muted">Database Connected</span>
                </div>
            </div>
        </div>
    </div>

    {{-- WIDGET 4: LAPORAN BARU --}}
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p class="text-muted mb-0 text-truncate">Laporan Baru</p>
                        <h3 class="text-dark mt-2 mb-0">{{ $pendingReportsCount }}</h3>
                    </div>
                    <div class="col-6">
                        <div class="ms-auto avatar-md bg-soft-danger rounded">
                            <iconify-icon icon="solar:bell-bing-bold" class="fs-32 avatar-title text-danger"></iconify-icon>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-danger me-1"><i class="bx bx-error"></i> {{ $criticalReports }} Pending</span>
                    <span class="text-muted">Perlu tindakan</span>
                </div>
            </div>
        </div>
    </div>
</div>

    {{-- TABEL 1: LOG AKTIVITAS (Menggunakan Data Reports Terbaru) --}}
   <div class="row">
    {{-- TABEL 1: LOG AKTIVITAS TERPUSAT (Reports, Comments, Likes) --}}
    <div class="col-xl-8">
        <div class="card card-height-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Log Aktivitas User</h4>
                <a href="#!" class="btn btn-sm btn-light">Lihat Semua</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 table-centered">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="py-2" style="width: 150px;">Waktu</th>
                                <th class="py-2">User</th>
                                <th class="py-2">Aktivitas</th>
                                <th class="py-2">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activities as $activity)
                            <tr>
                                {{-- KOLOM 1: WAKTU --}}
                                <td class="text-muted small">
                                    <i class="bx bx-time-five me-1"></i>
                                    {{ $activity->created_at->diffForHumans() }}
                                </td>

                                {{-- KOLOM 2: USER --}}
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs flex-shrink-0 me-2">
                                            <span class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                {{ substr($activity->user->name ?? 'G', 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-size-14">{{ $activity->user->name ?? 'Guest' }}</h6>
                                        </div>
                                    </div>
                                </td>

                                {{-- KOLOM 3: JENIS AKTIVITAS (ICON + LABEL) --}}
                                <td>
                                    @if($activity->type == 'report')
                                        <div class="badge badge-soft-danger font-size-12">
                                            <i class="bx bx-error me-1"></i> Melaporkan
                                        </div>
                                    @elseif($activity->type == 'comment')
                                        <div class="badge badge-soft-info font-size-12">
                                            <i class="bx bx-comment-detail me-1"></i> Berkomentar
                                        </div>
                                    @elseif($activity->type == 'like')
                                        <div class="badge badge-soft-danger font-size-12" style="background-color: #fde8e8; color: #e02424;">
                                            <i class="bx bxs-heart me-1"></i> Menyukai
                                        </div>
                                    @endif
                                </td>

                                {{-- KOLOM 4: DETAIL KONTEN --}}
                                <td>
                                    @if($activity->type == 'report')
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-danger">{{ $activity->reason }}</span>
                                            <small class="text-muted fst-italic">"{{ Str::limit($activity->details ?? $activity->comment, 30) }}"</small>
                                        </div>
                                    @elseif($activity->type == 'comment')
                                        <span class="text-dark">"{{ Str::limit($activity->content, 40) }}"</span>
                                        <small class="text-muted d-block">pada post #{{ $activity->image_id ?? '-' }}</small>
                                    @elseif($activity->type == 'like')
                                        <span class="text-muted">Menyukai postingan gambar <span class="fw-bold">#{{ $activity->image_id ?? 'ID' }}</span></span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bx bx-info-circle fs-4 mb-2"></i><br>
                                        Belum ada aktivitas user (Like, Comment, atau Report).
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TABEL 2: PENDAFTARAN USER BARU --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">User Baru Terdaftar</h4>
                <div class="d-flex gap-2">
                    {{-- Tombol Refresh (Tetap Ada) --}}
                    <button class="btn btn-sm btn-light"><i class="bx bx-refresh"></i> Refresh</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Tanggal Daftar</th>
                                <th>Status</th>
                                {{-- Kolom Aksi DIHAPUS --}}
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($newUsers as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        {{-- Avatar User --}}
                                        <div class="avatar-xs flex-shrink-0">
                                            <span class="avatar-title bg-soft-info text-info rounded-circle">
                                                {{ substr($user->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <span class="fw-semibold">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->created_at->format('d M Y') }}</td>
                                <td>
                                    @if($user->email_verified_at)
                                        <span class="badge badge-soft-success">Verified</span>
                                    @else
                                        <span class="badge badge-soft-warning">Unverified</span>
                                    @endif
                                </td>
                                {{-- Tombol Aksi DIHAPUS --}}
                            </tr>
                            @empty
                            <tr>
                                {{-- Colspan disesuaikan jadi 4 karena kolom aksi hilang --}}
                                <td colspan="4" class="text-center">Belum ada user terdaftar.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Script JS tambahan jika diperlukan
</script>
@endsection