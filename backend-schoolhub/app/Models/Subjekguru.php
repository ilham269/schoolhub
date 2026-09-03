<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subjekguru extends Model
{
    protected $table = 'teacher_subjects';

    protected $fillable = [
        'guru_id',
        'nama_pelajaran',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function subjek(): BelongsTo
    {
        return $this->belongsTo(Subjek::class, 'nama_pelajaran');
    }
}
