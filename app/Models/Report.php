<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    // Mengizinkan semua kolom diisi (kecuali id)
    protected $guarded = ['id'];

    /**
     * Relasi ke User (Siapa yang melapor)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Image (Gambar yang dilaporkan)
     */
    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    /**
     * Relasi ke Admin yang mereview laporan ini (jika ada)
     * Menggunakan kolom 'reviewed_by' sebagai foreign key
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}