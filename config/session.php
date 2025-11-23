<?php

use Illuminate\Support\Str;

return [

    // ... (kode lainnya)

    /*
    |--------------------------------------------------------------------------
    | Session Secure
    |--------------------------------------------------------------------------
    |
    | Pengaturan ini mengontrol apakah cookie harus dikirim hanya melalui HTTPS.
    | Di lingkungan development lokal (HTTP), ini harus diatur ke false.
    |
    */
    'secure' => env('SESSION_SECURE_COOKIE', false), // FIX KRITIS: DIUBAH MENJADI FALSE

    /*
    |--------------------------------------------------------------------------
    | Session SameSite
    |--------------------------------------------------------------------------
    |
    | Pengaturan ini menentukan nilai default cookie 'SameSite'. Hal ini dapat
    | digunakan untuk memitigasi serangan CSRF dan serangan penyertaan informasi
    | pengguna, tetapi juga dapat menyebabkan masalah integrasi.
    |
    */

    'same_site' => env('SESSION_SAME_SITE_COOKIE', 'lax'),

];