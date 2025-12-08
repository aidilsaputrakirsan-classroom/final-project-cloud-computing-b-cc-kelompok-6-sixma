<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    public function users()
    {
        // PERBAIKAN: Menggunakan Fully Qualified Class Name (FQCN) untuk memastikan Laravel 
        // menemukan Model User dari namespace App\Models.
        return $this->hasMany(\App\Models\User::class); 
    }
}