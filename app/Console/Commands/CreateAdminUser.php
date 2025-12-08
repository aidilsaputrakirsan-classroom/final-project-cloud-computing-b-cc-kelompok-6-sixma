<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Str;

class CreateAdminUser extends Command
{
    protected $signature = 'make:admin {email} {password}';
    protected $description = 'Membuat akun admin di Supabase Auth + Laravel Users';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $serviceKey = env('SUPABASE_SERVICE_ROLE_KEY');
        $projectUrl = env('SUPABASE_URL') . '/auth/v1/admin/users';

        if (!$serviceKey) {
            $this->error("❌ SUPABASE_SERVICE_ROLE_KEY belum di-setup");
            return;
        }

        $this->info("🚀 Membuat admin baru...");

        // 1) Buat user di Supabase Auth
        $response = Http::withHeaders([
            'apikey' => $serviceKey,
            'Authorization' => "Bearer {$serviceKey}",
            'Content-Type' => 'application/json',
        ])->post($projectUrl, [
            "email" => $email,
            "password" => $password,
            "email_confirm" => true,
            "user_metadata" => [
                "name" => "Admin"
            ]
        ]);

        if (!$response->successful()) {
            $this->error("❌ Gagal membuat user di Supabase");
            $this->error($response->body());
            return;
        }

        $supabaseUser = $response->json();
        $id = $supabaseUser['id'];

        $this->info("✔ User Supabase dibuat, ID: {$id}");

        // 2) Simpan ke tabel users Laravel
        User::updateOrCreate(
            ['id' => $id],
            [
                'email' => $email,
                'name' => 'Admin',
                'role_id' => 1,  // ADMIN
                'password' => bcrypt($password),
                'supabase_uuid' => $id,
            ]
        );

        $this->info("🎉 Admin berhasil dibuat!");
        $this->info("➡ Email: {$email}");
        $this->info("➡ Password: {$password}");
        $this->info("➡ Role: ADMIN");
    }
}
