@extends('layouts.admin-master', ['subtitle' => 'Admin Dashboard'])

@section('content')

{{-- Judul Halaman --}}
@include('layouts.partials/page-title', ['title' => 'Admin Panel', 'subtitle' => 'System Overview'])

<div class="row">
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p class="text-muted mb-0 text-truncate">Total User</p>
                        <h3 class="text-dark mt-2 mb-0">12,450</h3>
                    </div>
                    <div class="col-6">
                        <div class="ms-auto avatar-md bg-soft-primary rounded">
                            <iconify-icon icon="solar:users-group-rounded-bold" class="fs-32 avatar-title text-primary"></iconify-icon>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-success me-1"><i class="bx bx-up-arrow-alt"></i> 5.2%</span>
                    <span class="text-muted">Sejak bulan lalu</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p class="text-muted mb-0 text-truncate">Admin Aktif</p>
                        <h3 class="text-dark mt-2 mb-0">18</h3>
                    </div>
                    <div class="col-6">
                        <div class="ms-auto avatar-md bg-soft-info rounded">
                            <iconify-icon icon="solar:shield-user-bold" class="fs-32 avatar-title text-info"></iconify-icon>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted">Online saat ini: </span>
                    <span class="text-dark fw-bold">4</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p class="text-muted mb-0 text-truncate">Kesehatan Server</p>
                        <h3 class="text-dark mt-2 mb-0">98%</h3>
                    </div>
                    <div class="col-6">
                        <div class="ms-auto avatar-md bg-soft-success rounded">
                            <iconify-icon icon="solar:server-square-bold" class="fs-32 avatar-title text-success"></iconify-icon>
                        </div>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 5px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 98%" aria-valuenow="98" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p class="text-muted mb-0 text-truncate">Laporan Baru</p>
                        <h3 class="text-dark mt-2 mb-0">5</h3>
                    </div>
                    <div class="col-6">
                        <div class="ms-auto avatar-md bg-soft-danger rounded">
                            <iconify-icon icon="solar:bell-bing-bold" class="fs-32 avatar-title text-danger"></iconify-icon>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-danger me-1"><i class="bx bx-error"></i> 2 Kritis</span>
                    <span class="text-muted">Perlu tindakan</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Penggunaan Resource</h4>
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle btn btn-sm btn-outline-light" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="javascript:void(0);" class="dropdown-item">Refresh</a>
                        <a href="javascript:void(0);" class="dropdown-item">Detail</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h5 class="card-title mb-2">CPU Usage</h5>
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 45%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="fw-bold">45%</span>
                    </div>
                </div>
                <div class="mb-4">
                    <h5 class="card-title mb-2">Memory (RAM)</h5>
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height: 8px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 68%" aria-valuenow="68" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="fw-bold">68%</span>
                    </div>
                </div>
                <div class="mb-4">
                    <h5 class="card-title mb-2">Disk Space</h5>
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height: 8px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="fw-bold">25%</span>
                    </div>
                </div>
                
                <div class="alert alert-soft-warning mb-0" role="alert">
                    <i class="bx bx-info-circle me-1"></i> Jadwal backup berikutnya: <strong>02:00 AM</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card card-height-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Log Aktivitas Sistem</h4>
                <a href="#!" class="btn btn-sm btn-light">Lihat Semua Log</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 table-centered">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="py-2">Waktu</th>
                                <th class="py-2">User/Admin</th>
                                <th class="py-2">Aktivitas</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>10:45 AM</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="/images/users/avatar-2.jpg" class="avatar-xs rounded-circle me-2" alt="user">
                                        <div>
                                            <h6 class="mb-0">Budi Admin</h6>
                                            <small class="text-muted">Super Admin</small>
                                        </div>
                                    </div>
                                </td>
                                <td>Update Konfigurasi App</td>
                                <td><span class="badge badge-soft-success">Sukses</span></td>
                                <td class="text-muted">192.168.1.10</td>
                            </tr>
                            <tr>
                                <td>10:30 AM</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="/images/users/avatar-3.jpg" class="avatar-xs rounded-circle me-2" alt="user">
                                        <div>
                                            <h6 class="mb-0">Siti Editor</h6>
                                            <small class="text-muted">Content Mod</small>
                                        </div>
                                    </div>
                                </td>
                                <td>Hapus Artikel #404</td>
                                <td><span class="badge badge-soft-warning">Pending</span></td>
                                <td class="text-muted">192.168.1.15</td>
                            </tr>
                            <tr>
                                <td>09:15 AM</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs flex-shrink-0 me-2">
                                            <span class="avatar-title bg-soft-danger text-danger rounded-circle">S</span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">System</h6>
                                            <small class="text-muted">Otomatis</small>
                                        </div>
                                    </div>
                                </td>
                                <td>Gagal Backup Database</td>
                                <td><span class="badge badge-soft-danger">Error</span></td>
                                <td class="text-muted">Localhost</td>
                            </tr>
                            <tr>
                                <td>08:00 AM</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="/images/users/avatar-1.jpg" class="avatar-xs rounded-circle me-2" alt="user">
                                        <div>
                                            <h6 class="mb-0">Andi Manager</h6>
                                            <small class="text-muted">Admin</small>
                                        </div>
                                    </div>
                                </td>
                                <td>Login Dashboard</td>
                                <td><span class="badge badge-soft-success">Sukses</span></td>
                                <td class="text-muted">182.10.33.2</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Pendaftaran User Baru (Menunggu Verifikasi)</h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-danger"><i class="bx bx-x"></i> Tolak Semua</button>
                    <button class="btn btn-sm btn-success"><i class="bx bx-check"></i> Setujui Semua</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                    </div>
                                </th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Tanggal Daftar</th>
                                <th>Role Request</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="user1">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="/images/users/avatar-4.jpg" alt="" class="avatar-xs rounded-circle">
                                        <span class="fw-semibold">Dimas Anggara</span>
                                    </div>
                                </td>
                                <td>dimas@example.com</td>
                                <td>24 Jan 2025</td>
                                <td>Editor</td>
                                <td>
                                    <button class="btn btn-sm btn-soft-success"><i class="bx bx-check"></i></button>
                                    <button class="btn btn-sm btn-soft-danger"><i class="bx bx-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="user2">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="/images/users/avatar-5.jpg" alt="" class="avatar-xs rounded-circle">
                                        <span class="fw-semibold">Rina Melati</span>
                                    </div>
                                </td>
                                <td>rina.mel@example.com</td>
                                <td>23 Jan 2025</td>
                                <td>Author</td>
                                <td>
                                    <button class="btn btn-sm btn-soft-success"><i class="bx bx-check"></i></button>
                                    <button class="btn btn-sm btn-soft-danger"><i class="bx bx-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
{{-- Jika Anda ingin menambahkan chart, tambahkan JS file disini --}}
{{-- @vite(['resources/js/pages/admin-dashboard.js']) --}}
<script>
    // Contoh script inline sederhana jika diperlukan (misal untuk select all checkbox)
    document.getElementById('checkAll').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('.form-check-input');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    });
</script>
@endsection