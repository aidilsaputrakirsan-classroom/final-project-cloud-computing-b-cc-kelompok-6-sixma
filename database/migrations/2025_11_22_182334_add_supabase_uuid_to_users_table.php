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
                // Tambahkan kolom untuk menyimpan UUID dari Supabase.
                // UUID harus berupa string dan bisa null (jika kamu membolehkan user lama tanpa Supabase).
                // Berdasarkan error kamu, kolom ini di anggap NOT NULL, jadi kita buat nullable dulu.
                $table->uuid('supabase_uuid')->nullable()->unique();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('supabase_uuid');
            });
        }
    };