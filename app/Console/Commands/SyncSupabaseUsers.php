<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Str;

class SyncSupabaseUsers extends Command
{
    protected $signature = 'sync:supabase-users';
    protected $description = 'Sinkronisasi user dari Supabase Auth ke tabel users Laravel';

    public function handle()
    {
        $serviceKey = env('SUPABASE_SERVICE_ROLE_KEY');
        $projectUrl = env('SUPABASE_URL');

        $url = "{$projectUrl}/auth/v1/admin/users";

        $this->info("🔄 Mengambil data user dari Supabase Auth...");

        $response = Http::withHeaders([
            'apikey'        => $serviceKey,
            'Authorization' => "Bearer {$serviceKey}",
        ])->get($url);

        $this->info("🔑 Service Key Prefix: " . substr($serviceKey, 0, 10) . "...");

        if ($response->status() !== 200) {
            $this->error("❌ Supabase mengembalikan error.");
            $this->line($response->body());
            return;
        }

        $data = $response->json();
        $users = $data['users'] ?? [];

        $this->info("✔ Total user ditemukan: " . count($users));

        foreach ($users as $u) {

            $id    = $u['id'];
            $email = $u['email'] ?? null;
            $name  = $u['user_metadata']['name'] ?? 'User';

            if (!$email) continue;

            $this->info("🔧 Sinkronisasi user: {$email}");

            // Cari user berdasarkan email
            $user = User::where('email', $email)->first();

            if ($user) {
                // Update user lama
                $user->update([
                    'id'      => $id,            // samakan UUID
                    'name'    => $name,
                    'role_id' => $user->role_id ?? 2, // jangan timpa admin
                ]);

                $this->info("🔁 Update user: {$email}");
            } else {
                // Insert user baru
                User::create([
                    'id'       => $id,
                    'email'    => $email,
                    'name'     => $name,
                    'role_id'  => 2,                      // default user
                    'password' => bcrypt(Str::random(12)),
                ]);

                $this->info("🆕 Insert user baru: {$email}");
            }
        }

        $this->info("🎉 Sinkronisasi selesai!");
    }
}
