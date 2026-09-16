<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengumpulantugas extends Model
{
    protected $table = 'pengumpulan_tugas';

    protected $fillable = [
        'tugas_id',
        'murid_id',
        'file_path',
        'link',
        'catatan',
        'tanggal_pengumpulan',
        'nilai',
        'feedback',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengumpulan' => 'datetime',
            'nilai' => 'decimal:2',
        ];
    }

    public function tugas(): BelongsTo
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }

    public function murid(): BelongsTo
    {
        return $this->belongsTo(Murid::class, 'murid_id');
    }
}
