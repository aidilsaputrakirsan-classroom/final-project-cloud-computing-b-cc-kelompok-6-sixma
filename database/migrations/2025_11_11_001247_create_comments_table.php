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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke User (Siapa yang berkomentar)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Relasi ke Post (Postingan mana yang dikomentari)
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            
            // Isi Komentar
            $table->text('content');
            
            // Fitur Reply/Balasan Komentar (Self-referencing relationship)
            // Jika null = komentar utama. Jika ada isinya = balasan untuk komentar lain.
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');
            
            // Penanda status baca untuk notifikasi pemilik post
            $table->boolean('is_read')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};