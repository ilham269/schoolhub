<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subjekkelas extends Model
{
    protected $table = 'class_subjects';

    protected $fillable = [
        'kelas_id',
        'nama_pelajaran',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function subjek(): BelongsTo
    {
        return $this->belongsTo(Subjek::class, 'nama_pelajaran');
    }
}