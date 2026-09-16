<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subjek extends Model
{
    use HasFactory;

    protected $table = 'mapels';

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'deskripsi',
        'jumlah_jam',
        'kkm',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_jam' => 'integer',
            'kkm' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function subjekkelas(): HasMany
    {
        return $this->hasMany(Subjekkelas::class, 'mapel_id');
    }

    public function subjekgurus(): HasMany
    {
        return $this->hasMany(Subjekguru::class, 'mapel_id');
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'mapel_id');
    }

    public function materis(): HasMany
    {
        return $this->hasMany(Materi::class, 'mapel_id');
    }

    public function tugas(): HasMany
    {
        return $this->hasMany(Tugas::class, 'mapel_id');
    }

    public function gurus(): BelongsToMany
    {
        return $this->belongsToMany(Guru::class, 'teacher_subjects', 'mapel_id', 'guru_id')
            ->withTimestamps();
    }

    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'class_subjects', 'mapel_id', 'kelas_id')
            ->withTimestamps();
    }
}
