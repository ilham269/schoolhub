<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tugas extends Model
{
    protected $table = 'tugas';

    protected $fillable = [
        'kelas_id',
        'guru_id',
        'mapel_id',
        'materi_id',
        'judul',
        'deskripsi',
        'instruksi',
        'file_path',
        'tanggal_dibuat',
        'deadline',
        'nilai_maksimal',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_dibuat' => 'datetime',
            'deadline' => 'datetime',
            'is_active' => 'boolean',
            'nilai_maksimal' => 'integer',
        ];
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Subjek::class, 'mapel_id');
    }

    public function materi(): BelongsTo
    {
        return $this->belongsTo(Materi::class, 'materi_id');
    }

    public function pengumpulan(): HasMany
    {
        return $this->hasMany(PengumpulanTugas::class, 'tugas_id');
    }
}