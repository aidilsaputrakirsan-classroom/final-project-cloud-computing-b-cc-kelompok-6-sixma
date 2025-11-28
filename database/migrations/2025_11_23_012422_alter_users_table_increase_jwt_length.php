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
        Schema::table('users', function (Blueprint $table) {
            // Mengubah tipe kolom supabase_jwt menjadi TEXT karena JWT terlalu panjang 
            // untuk VARCHAR(255).
            // Tipe TEXT adalah tipe data string yang sangat panjang di PostgreSQL.
            $table->text('supabase_jwt')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Mengembalikan kolom ke VARCHAR(255) (atau tipe defaultnya)
            // Catatan: Jika ada data yang lebih panjang dari 255, operasi ini akan gagal.
            $table->string('supabase_jwt', 255)->nullable()->change();
        });
    }
};