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
        // SKENARIO 1: Jika tabel BELUM ADA, buat dari awal (bersih)
        if (!Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('image_id')->constrained('images')->onDelete('cascade');
                $table->string('reason');
                $table->text('details')->nullable();
                $table->enum('status', ['pending', 'reviewed', 'resolved'])->default('pending');
                $table->foreignUuid('reviewed_by')->nullable()->constrained('users');
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        } 
        
        // SKENARIO 2: Jika tabel SUDAH ADA (Kasus Anda di Supabase)
        else {
            Schema::table('reports', function (Blueprint $table) {
                // 1. Ubah kolom 'post_id' (lama) menjadi 'image_id' (baru) jika ada
                if (Schema::hasColumn('reports', 'post_id')) {
                    // Kita drop foreign key lama dulu agar aman (nama constraint biasanya reports_post_id_foreign)
                    // Jika error, bisa dikomentari baris dropForeign ini
                    try {
                        $table->dropForeign(['post_id']); 
                    } catch (\Exception $e) {}

                    $table->renameColumn('post_id', 'image_id');
                    
                    // Tambahkan constraint baru ke tabel images
                    // Pastikan tipe data sama (bigInteger/foreignId)
                    try {
                        $table->foreign('image_id')->references('id')->on('images')->onDelete('cascade');
                    } catch (\Exception $e) {}
                }

                // 2. Ubah kolom 'category' menjadi 'reason' jika ada
                if (Schema::hasColumn('reports', 'category')) {
                    $table->renameColumn('category', 'reason');
                }

                // 3. Ubah kolom 'comment' menjadi 'details' jika ada
                if (Schema::hasColumn('reports', 'comment')) {
                    $table->renameColumn('comment', 'details');
                }
                
                // 4. Pastikan kolom user_id bertipe UUID (jika perlu penyesuaian manual)
                // Karena mengubah tipe data foreign key agak riskan di migration otomatis, 
                // kita asumsikan tipe data di Supabase sudah benar (UUID).
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};