<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jadwal extends Model
{
    protected $fillable = [
        'kelas_id',
        'guru_id',
        'mapel_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'ruang',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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

    public function subjek(): BelongsTo
    {
        return $this->belongsTo(Subjek::class, 'mapel_id');
    }

    public function scopeOverlapping(Builder $query, string $hari, string $start, string $end, ?int $excludeId = null): Builder
    {
        $query->where('hari', $hari)
            ->where('jam_mulai', '<', $end)
            ->where('jam_selesai', '>', $start);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query;
    }

    public static function teacherHasConflict(int $guruId, string $hari, string $start, string $end, ?int $excludeId = null): bool
    {
        return static::query()
            ->where('guru_id', $guruId)
            ->overlapping($hari, $start, $end, $excludeId)
            ->exists();
    }

    public static function classHasConflict(int $kelasId, string $hari, string $start, string $end, ?int $excludeId = null): bool
    {
        return static::query()
            ->where('kelas_id', $kelasId)
            ->overlapping($hari, $start, $end, $excludeId)
            ->exists();
    }
}
