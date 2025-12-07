<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Image;  
use App\Models\Report; 
use App\Models\Comment; // <--- Tambahkan ini
use App\Models\Like;    // <--- Tambahkan ini
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1. STATISTIK UTAMA
        $totalUsers = User::count();
        $usersLastMonth = User::where('created_at', '<=', Carbon::now()->subMonth())->count();
        $userGrowth = ($usersLastMonth > 0) ? (($totalUsers - $usersLastMonth) / $usersLastMonth) * 100 : 0;

        $onlineUsers = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', Carbon::now()->subMinutes(5)->timestamp)
            ->distinct('user_id')
            ->count('user_id');

        $totalImages = Image::count();
        
        // Data Pending Reports untuk Widget
        $pendingReportsCount = 0;
        $criticalReports = 0;
        try {
            $pendingReportsCount = Report::where('status', 'pending')->count();
            $criticalReports = Report::where('status', 'pending')->count(); 
        } catch (\Exception $e) {}


        // 2. LOG AKTIVITAS TERPUSAT (REPORTS + COMMENTS + LIKES)
        $activities = collect(); // Koleksi kosong awal

        try {
            // A. Ambil Data REPORTS
            $reports = Report::with('user')->latest()->take(10)->get()->map(function($item) {
                $item->type = 'report'; // Tandai sebagai report
                return $item;
            });
            
            // B. Ambil Data COMMENTS
            $comments = Comment::with(['user', 'image'])->latest()->take(10)->get()->map(function($item) {
                $item->type = 'comment'; // Tandai sebagai comment
                return $item;
            });

            // C. Ambil Data LIKES
            $likes = Like::with(['user', 'image'])->latest()->take(10)->get()->map(function($item) {
                $item->type = 'like'; // Tandai sebagai like
                return $item;
            });

            // D. GABUNGKAN & URUTKAN
            // Gabung semua, urutkan descending by created_at, ambil 10 teratas
            $activities = $reports->concat($comments)->concat($likes)
                ->sortByDesc('created_at')
                ->take(10);

        } catch (\Exception $e) {
            // Fallback jika tabel belum lengkap
        }

        // 3. USER BARU
        $newUsers = User::latest()
        ->take(20) 
        ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'userGrowth',
            'onlineUsers',
            'totalImages',
            'pendingReportsCount',
            'criticalReports',
            'activities', // <--- Variabel baru yang dikirim ke view (menggantikan recentActivities)
            'newUsers'
        ));
    }
}