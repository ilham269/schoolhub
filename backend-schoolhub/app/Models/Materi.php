<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materi extends Model
{
    protected $table = 'materis';

    protected $fillable = [
        'kelas_id',
        'guru_id',
        'mapel_id',
        'judul',
        'deskripsi',
        'konten',
        'file_path',
        'link',
        'tanggal_upload',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_upload' => 'date',
            'is_published'   => 'boolean',
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

    public function tugas(): HasMany
    {
        return $this->hasMany(Tugas::class, 'materi_id');
    }
}
