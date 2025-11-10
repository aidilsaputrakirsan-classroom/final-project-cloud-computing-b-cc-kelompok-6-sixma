<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $supabaseUrl = env('SUPABASE_REST_URL') . '/images';
        $supabaseKey = env('SUPABASE_API_KEY');

        // Ambil gambar milik user yang sedang login
        $response = Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey
        ])->get($supabaseUrl . '?user_id=eq.' . $user->id);

        $images = $response->json() ?? [];

        return view('profile.index', compact('user', 'images'));
    }
}
