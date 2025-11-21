<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Konfigurasi untuk UUID Supabase
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',             // 👈 Menerima UUID dari Supabase
        'name',
        'email',
        'password',
        'supabase_jwt',   // 👈 Menerima JWT
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
     * Memaksa Laravel memuat ulang data dari DB untuk memastikan JWT tersedia di sesi.
     *
     * @var list<string>
     */
    protected $visible = [
        'id', 
        'name',
        'email',
        'supabase_jwt', 
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
        ];
    }
}