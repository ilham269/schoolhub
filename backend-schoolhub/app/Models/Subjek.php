<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'code',
        'nama_pelajaran',
        'deskripsi',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
    public function subjekkelass()
    {
    return $this->hasMany(Subjek-kelas::class, 'Nama_pelajaran');
    }
    public function subjekguru()
    {
    return $this->hasMany(Subjekguru::class, 'nama_pelajaran');
    }
    public function jadwals()
    {
    return $this->hasMany(Jadwal::class, 'nama_pelajaran');
    }
}