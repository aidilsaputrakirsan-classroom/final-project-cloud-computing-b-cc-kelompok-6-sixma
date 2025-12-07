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
        // SKENARIO 1: Jika tabel BELUM ADA, buat dari awal dengan struktur yang BENAR
        if (!Schema::hasTable('likes')) {
            Schema::create('likes', function (Blueprint $table) {
                $table->id();
                
                // Gunakan UUID karena user kamu pakai UUID
                $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
                
                // Gunakan image_id sesuai Controller (bukan post_id)
                $table->foreignId('image_id')->constrained('images')->onDelete('cascade');
                
                $table->timestamps();
            });
        } 
        
        // SKENARIO 2: Jika tabel SUDAH ADA (Supabase)
        else {
            Schema::table('likes', function (Blueprint $table) {
                // 1. Perbaiki kolom 'post_id' jika masih ada
                if (Schema::hasColumn('likes', 'post_id')) {
                    // Hapus foreign key lama jika ada
                    try { $table->dropForeign(['post_id']); } catch (\Exception $e) {}
                    
                    // Rename ke image_id
                    $table->renameColumn('post_id', 'image_id');
                    
                    // Tambahkan foreign key baru ke images
                    try {
                         $table->foreign('image_id')->references('id')->on('images')->onDelete('cascade');
                    } catch (\Exception $e) {}
                }

                // 2. Jika kolom image_id belum ada (dan post_id juga ga ada), buat baru
                if (!Schema::hasColumn('likes', 'image_id') && !Schema::hasColumn('likes', 'post_id')) {
                    $table->foreignId('image_id')->constrained('images')->onDelete('cascade');
                }

                // 3. Hapus kolom 'is_read' jika ada (biasanya tidak dipakai di tabel likes murni)
                if (Schema::hasColumn('likes', 'is_read')) {
                    $table->dropColumn('is_read');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};