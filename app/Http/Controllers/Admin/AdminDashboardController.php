<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    private function getServiceHeaders() 
    {
        $serviceKey = env('SUPABASE_SERVICE_KEY') ?? env('SUPABASE_ANON_KEY'); 
        
        return [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . $serviceKey,
            'Content-Type' => 'application/json',
            'Prefer' => 'count=exact'
        ];
    }

    private function parseContentRange($contentRange)
    {
        if (!$contentRange) {
            return 0;
        }
        preg_match('/\/(\d+)$/', $contentRange, $matches);
        return isset($matches[1]) ? (int)$matches[1] : 0;
    }

    private function getTableCount($tableName, $filters = [])
    {
        $headers = $this->getServiceHeaders();
        $baseUrl = env('SUPABASE_REST_URL');
        
        try {
            $url = "{$baseUrl}/{$tableName}?select=id&limit=1";
            
            foreach ($filters as $key => $value) {
                $url .= "&{$key}={$value}";
            }

            $response = Http::withHeaders($headers)
                ->withoutVerifying()
                ->timeout(10)
                ->get($url);

            if ($response->successful()) {
                return $this->parseContentRange($response->header('Content-Range'));
            }

            return 0;

        } catch (\Exception $e) {
            Log::error("Error getting count for {$tableName}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Mengambil aktivitas dari berbagai tabel dan menggabungkannya
     */
    private function getActivityLogs()
    {
        $headers = $this->getServiceHeaders();
        $baseUrl = env('SUPABASE_REST_URL');
        $activities = [];

        try {
            // 1. Upload Images (10 terbaru)
            $imagesResponse = Http::withHeaders($headers)
                ->withoutVerifying()
                ->timeout(15)
                ->get($baseUrl . '/images?select=id,title,image_path,created_at,user_id,users:user_id(name,email),categories:category_id(name)&order=created_at.desc&limit=10');
            
            if ($imagesResponse->successful()) {
                $storageUrl = env('SUPABASE_URL') . '/storage/v1/object/public/images/';
                
                foreach ($imagesResponse->json() as $image) {
                    $activities[] = [
                        'waktu' => isset($image['created_at']) ? Carbon::parse($image['created_at'])->format('d/m/Y H:i') : '-',
                        'user_name' => $image['users']['name'] ?? 'Unknown User',
                        'user_email' => $image['users']['email'] ?? '-',
                        'tipe' => 'Upload',
                        'raw_type' => 'upload',
                        'detail' => 'Mengupload karya: ' . ($image['title'] ?? 'Tanpa Judul'),
                        'postingan_title' => $image['title'] ?? '-',
                        'postingan_url' => isset($image['image_path']) ? $storageUrl . $image['image_path'] : null,
                        'category' => $image['categories']['name'] ?? '-',
                        'created_timestamp' => strtotime($image['created_at'] ?? 'now'),
                    ];
                }
            }

            // 2. Comments (10 terbaru)
            $commentsResponse = Http::withHeaders($headers)
                ->withoutVerifying()
                ->timeout(15)
                ->get($baseUrl . '/comments?select=id,content,created_at,user_id,image_id,users:user_id(name,email),images:image_id(title,image_path)&order=created_at.desc&limit=10');
            
            if ($commentsResponse->successful()) {
                $storageUrl = env('SUPABASE_URL') . '/storage/v1/object/public/images/';
                
                foreach ($commentsResponse->json() as $comment) {
                    $activities[] = [
                        'waktu' => isset($comment['created_at']) ? Carbon::parse($comment['created_at'])->format('d/m/Y H:i') : '-',
                        'user_name' => $comment['users']['name'] ?? 'Unknown User',
                        'user_email' => $comment['users']['email'] ?? '-',
                        'tipe' => 'Comment',
                        'raw_type' => 'comment',
                        'detail' => 'Berkomentar: ' . (strlen($comment['content'] ?? '') > 50 ? substr($comment['content'], 0, 50) . '...' : ($comment['content'] ?? '-')),
                        'postingan_title' => $comment['images']['title'] ?? 'Postingan Dihapus',
                        'postingan_url' => isset($comment['images']['image_path']) ? $storageUrl . $comment['images']['image_path'] : null,
                        'category' => '-',
                        'created_timestamp' => strtotime($comment['created_at'] ?? 'now'),
                    ];
                }
            }

            // 3. Likes (10 terbaru)
            $likesResponse = Http::withHeaders($headers)
                ->withoutVerifying()
                ->timeout(15)
                ->get($baseUrl . '/likes?select=id,created_at,user_id,image_id,users:user_id(name,email),images:image_id(title,image_path)&order=created_at.desc&limit=10');
            
            if ($likesResponse->successful()) {
                $storageUrl = env('SUPABASE_URL') . '/storage/v1/object/public/images/';
                
                foreach ($likesResponse->json() as $like) {
                    $activities[] = [
                        'waktu' => isset($like['created_at']) ? Carbon::parse($like['created_at'])->format('d/m/Y H:i') : '-',
                        'user_name' => $like['users']['name'] ?? 'Unknown User',
                        'user_email' => $like['users']['email'] ?? '-',
                        'tipe' => 'Like',
                        'raw_type' => 'like',
                        'detail' => 'Menyukai karya: ' . ($like['images']['title'] ?? 'Tanpa Judul'),
                        'postingan_title' => $like['images']['title'] ?? 'Postingan Dihapus',
                        'postingan_url' => isset($like['images']['image_path']) ? $storageUrl . $like['images']['image_path'] : null,
                        'category' => '-',
                        'created_timestamp' => strtotime($like['created_at'] ?? 'now'),
                    ];
                }
            }

            // 4. Reports (10 terbaru)
            $reportsResponse = Http::withHeaders($headers)
                ->withoutVerifying()
                ->timeout(15)
                ->get($baseUrl . '/reports?select=id,reason,status,created_at,image_id,user_id,images:image_id(title,image_path),users:user_id(name,email)&order=created_at.desc&limit=10');
            
            if ($reportsResponse->successful()) {
                $storageUrl = env('SUPABASE_URL') . '/storage/v1/object/public/images/';
                
                foreach ($reportsResponse->json() as $report) {
                    $activities[] = [
                        'waktu' => isset($report['created_at']) ? Carbon::parse($report['created_at'])->format('d/m/Y H:i') : '-',
                        'user_name' => $report['users']['name'] ?? 'Unknown User',
                        'user_email' => $report['users']['email'] ?? '-',
                        'tipe' => 'Report',
                        'raw_type' => 'report',
                        'detail' => 'Melaporkan: ' . ($report['reason'] ?? 'Tanpa alasan'),
                        'postingan_title' => $report['images']['title'] ?? 'Postingan Dihapus',
                        'postingan_url' => isset($report['images']['image_path']) ? $storageUrl . $report['images']['image_path'] : null,
                        'category' => 'Status: ' . ($report['status'] ?? 'pending'),
                        'created_timestamp' => strtotime($report['created_at'] ?? 'now'),
                    ];
                }
            }

            // Sort berdasarkan waktu terbaru
            usort($activities, function($a, $b) {
                return $b['created_timestamp'] - $a['created_timestamp'];
            });

            // Ambil 15 aktivitas terbaru
            return array_slice($activities, 0, 15);

        } catch (\Exception $e) {
            Log::error('Error fetching activity logs: ' . $e->getMessage());
            return [];
        }
    }

    public function index()
    {
        // filter: all|upload|comment|like|report
        $filterType = request('type', 'all');
        $cacheKey = 'admin_stats_' . $filterType;

        $stats = Cache::remember($cacheKey, 300, function () use ($filterType) {
            try {
                $totalUsers = $this->getTableCount('users');
                $totalActivities = $this->getTableCount('images');
                
                $lastMonth = Carbon::now()->subDays(30)->toIso8601String();
                $newUsersCount = $this->getTableCount('users', [
                    'created_at' => "gte.{$lastMonth}"
                ]);
                
                $allLogs = $this->getActivityLogs();

                if ($filterType === 'all') {
                    $activityLogs = $allLogs;
                } else {
                    $activityLogs = array_values(array_filter($allLogs, function ($log) use ($filterType) {
                        return ($log['raw_type'] ?? null) === $filterType;
                    }));
                }

                $adminAktif = $this->getTableCount('users', [
                    'role' => 'eq.admin'
                ]);

                return [
                    'totalUsers' => $totalUsers,
                    'totalActivities' => $totalActivities,
                    'newUsersCount' => $newUsersCount,
                    'activityLogs' => $activityLogs,
                    'adminAktif' => $adminAktif,
                ];
                
            } catch (\Exception $e) {
                Log::error('Admin Dashboard Stats Error: ' . $e->getMessage());
                
                return [
                    'totalUsers' => 0,
                    'totalActivities' => 0,
                    'newUsersCount' => 0,
                    'activityLogs' => [],
                    'adminAktif' => 0,
                ];
            }
        });

        return view('admin.dashboard', [
            'totalUsers' => $stats['totalUsers'],
            'totalActivities' => $stats['totalActivities'],
            'newUsersCount' => $stats['newUsersCount'],
            'activityLogs' => $stats['activityLogs'],
            'adminAktif' => $stats['adminAktif'],
            'filterType' => $filterType,
        ]);
    }

    public function clearCache()
    {
        foreach (['all','upload','comment','like','report'] as $type) {
            Cache::forget('admin_stats_' . $type);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', 'Cache dashboard berhasil dibersihkan!');
    }
}
