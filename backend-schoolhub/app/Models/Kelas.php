<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'name',
        'kelas',
        'jurusan',
        'angkatan',
    ];

    // Satu kelas mempunyai banyak murid
    public function murids()
    {
        return $this->hasMany(Murid::class, 'kelas_id');
    }
}