<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

function logActivity($action, $resourceId, $resourceTitle, $details = [])
{
    Http::withHeaders([
        'apikey' => env('SUPABASE_SERVICE_ROLE_KEY'),
        'Authorization' => 'Bearer ' . env('SUPABASE_SERVICE_ROLE_KEY'),
        'Content-Type' => 'application/json'
    ])->post(env('SUPABASE_REST_URL') . '/activity_logs', [
        'user_id' => Auth::id(),
        'action_type' => $action,
        'resource_id' => $resourceId,
        'resource_title' => $resourceTitle,
        'details' => $details
    ]);
}
