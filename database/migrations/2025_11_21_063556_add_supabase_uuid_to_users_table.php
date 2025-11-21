<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan tabel 'users' ada sebelum mencoba menambah kolom
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Tambahkan kolom untuk menyimpan UUID dari Supabase
                // Menggunakan tipe string karena PostgreSQL/Laravel UUID tidak selalu kompatibel
                // Tipe data ini adalah 'uuid' di PostgreSQL.
                $table->uuid('supabase_uuid')->nullable()->unique()->after('password');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Hapus kolom jika migrasi di-rollback
                $table->dropColumn('supabase_uuid');
            });
        }
    }
};