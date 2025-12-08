<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Mengambil JWT Admin (Service Key atau JWT user dengan role admin)
     * Karena dashboard biasanya butuh data semua user, Service Key lebih disarankan.
     * Jika tidak ada Service Key, kita akan menggunakan Anon Key.
     */
    private function getSupabaseHeaders()
    {
        // Menggunakan Anon Key untuk request COUNT, yang umumnya diperbolehkan
        $key = env('SUPABASE_ANON_KEY');
        return [
            'apikey' => $key,
            'Authorization' => 'Bearer ' . $key,
            'Content-Type' => 'application/json',
        ];
    }
    
    // Asumsi: Kamu akan membuat Admin Dashboard menggunakan Service Key atau JWT Admin
    private function getServiceHeaders() 
    {
        // Gunakan Service Key untuk akses data level tinggi jika ada
        $key = env('SUPABASE_SERVICE_KEY') ?? env('SUPABASE_ANON_KEY'); 
        return [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . $key,
            'Content-Type' => 'application/json',
        ];
    }

    public function index()
    {
        $headers = $this->getServiceHeaders();
        $base_url = env('SUPABASE_REST_URL');

        // 🛑 Gunakan Cache untuk statistik agar Dashboard cepat dimuat
        $stats = Cache::remember('admin_stats', 60, function () use ($headers, $base_url) {
            
            // 1. Total Users (Count semua user, RLS harus diabaikan/diizinkan)
            $usersRes = Http::withHeaders($headers)
                ->withoutVerifying()
                ->get($base_url . '/users?select=count');
            $totalUsers = $usersRes->successful() ? (int)$usersRes->header('Content-Range') : 0;
            
            // 2. Total Posts/Aktivitas (Count images)
            $imagesRes = Http::withHeaders($headers)
                ->withoutVerifying()
                ->get($base_url . '/images?select=count');
            $totalActivities = $imagesRes->successful() ? (int)$imagesRes->header('Content-Range') : 0;
            
            // 3. User yang terdaftar bulan ini (Contoh untuk growth)
            $lastMonth = Carbon::now()->subMonth()->toIso8601String();
            $newUsersRes = Http::withHeaders($headers)
                ->withoutVerifying()
                ->get($base_url . "/users?select=count&created_at=gte.$lastMonth");
            $newUsersCount = $newUsersRes->successful() ? (int)$newUsersRes->header('Content-Range') : 0;
            
            // 4. Log Aktivitas (Contoh data dari Reports, Comments, Likes - Gabungan)
            // *Ini sangat kompleks di REST API. Kita akan simulasikan pengambilan 10 aktivitas terakhir*
            // Kita akan ambil data Reports sebagai contoh Log Aktivitas:
            $reportsRes = Http::withHeaders($headers)
                ->withoutVerifying()
                ->get($base_url . '/reports?select=*,images:image_id(title),users:user_id(name)&order=created_at.desc&limit=10');
            $activityLogs = $reportsRes->successful() ? $reportsRes->json() : [];

            return [
                'totalUsers' => $totalUsers,
                'totalActivities' => $totalActivities,
                'newUsersCount' => $newUsersCount,
                'activityLogs' => $activityLogs
            ];
        });

        return view('admin.dashboard', [
            'totalUsers' => $stats['totalUsers'],
            'totalActivities' => $stats['totalActivities'],
            'newUsersCount' => $stats['newUsersCount'],
            'activityLogs' => $stats['activityLogs'], // Ini log aktivitas real dari tabel reports
        ]);
    }
}