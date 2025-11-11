<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            // Konten komentar
            $table->text('content'); 

            // Foreign Key ke tabel images
            $table->foreignId('image_id')->constrained('images')->onDelete('cascade');

            // Foreign Key ke tabel users (siapa yang komentar)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
