<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Tambahkan ini
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
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
                $view->with('notifCount', NotificationService::unreadCount(Auth::id()));
            } else {
                $view->with('notifCount', 0);
            }
        });
    }
}
