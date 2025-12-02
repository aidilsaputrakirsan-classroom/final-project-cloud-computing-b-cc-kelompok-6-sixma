<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Tambahkan ini
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache; // DITAMBAH: Untuk caching
use App\Services\NotificationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $userId = Auth::id();
                
                // FIX LAG: Menggunakan Cache::remember untuk menyimpan hitungan selama 10 menit (600 detik)
                $notifCount = Cache::remember('user_notif_count_' . $userId, 600, function () use ($userId) {
                    // Hanya panggil service yang lambat ini jika cache sudah kadaluwarsa
                    return NotificationService::unreadCount($userId);
                });
                
                $view->with('notifCount', $notifCount);
            } else {
                $view->with('notifCount', 0);
            }
        });
    }
}