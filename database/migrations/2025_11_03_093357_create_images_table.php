<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            
            // 🚨 PERBAIKAN 1: Mengubah Foreign Key user_id dari integer menjadi UUID
            // Menggunakan string/uuid sebagai tipe data dasar untuk kolom user_id
            $table->uuid('user_id')->nullable(); 

            // Kolom category_id tetap karena menunjuk ke categories.id (asumsi masih integer)
            $table->unsignedBigInteger('category_id')->nullable();
            
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('image_path');
            $table->timestamps();

            // 🚨 PERBAIKAN 2: Mendefinisikan Foreign Key untuk UUID
            // Mengganti cara penulisan Foreign Key agar sesuai dengan UUID
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->nullOnDelete();
                
            // Foreign Key category_id tetap
            $table->foreign('category_id')
                ->references('id')->on('categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};