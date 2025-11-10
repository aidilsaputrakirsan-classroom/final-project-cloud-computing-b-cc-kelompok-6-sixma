<?php
// database/migrations/*_create_reports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            // Siapa yang melaporkan (FK ke users)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); 

            // Konten yang dilaporkan (FK ke images)
            $table->foreignId('image_id')->constrained('images')->onDelete('cascade'); 

            $table->string('reason_category'); // Misal: Plagiat, SARA
            $table->text('description')->nullable(); 

            // Status Laporan (Untuk Dashboard Admin)
            $table->enum('status', ['new', 'reviewed', 'resolved', 'rejected'])->default('new'); 

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};