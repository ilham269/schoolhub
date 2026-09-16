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
        'wali_kelas',
        'kapasitas',
    ];

    // Satu kelas mempunyai banyak murid
    public function murids()
    {
        return $this->hasMany(Murid::class, 'kelas_id');
    }
    
    // Relasi ke user wali kelas
    public function waliKelas()
    {
        return $this->belongsTo(User::class, 'wali_kelas');
    }

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class, 'kelas_id');
    }

    public function subjekkelas()
    {
        return $this->hasMany(Subjekkelas::class, 'kelas_id');
    }

    public function subjeks()
    {
        return $this->belongsToMany(Subjek::class, 'class_subjects', 'kelas_id', 'mapel_id')
            ->withTimestamps();
    }
}