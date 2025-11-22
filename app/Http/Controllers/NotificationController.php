<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $notifications = Http::withHeaders([
            'apikey'        => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . "/notifications?user_id=eq.$userId&order=created_at.desc");

        return view('profile.notifications', [
            'notifications' => $notifications->json()
        ]);
    }
}
