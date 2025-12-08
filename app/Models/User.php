<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// Hapus baris: use App\Models\Role; <-- Kita tidak memerlukannya karena menggunakan FQCN di bawah

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // FIX KRITIS: Tetapkan Primary Key sebagai ID.
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'role',
        'supabase_uuid',
        'supabase_jwt',     
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'supabase_uuid' => 'string',
        ];
    }

    public function role()
    {
        // PERBAIKAN: Menggunakan Fully Qualified Class Name (FQCN) untuk memastikan Laravel 
        // menemukan Model Role terlepas dari masalah Autoloading.
        return $this->belongsTo(\App\Models\Role::class); 
    }
}