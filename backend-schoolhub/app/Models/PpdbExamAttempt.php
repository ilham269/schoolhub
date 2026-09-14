<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpdbExamAttempt extends Model
{
    protected $fillable = [
        'exam_id',
        'calon_siswa_id',
        'started_at',
        'submitted_at',
        'score',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'score' => 'decimal:2',
        ];
    }

    public function exam()
    {
        return $this->belongsTo(PpdbExam::class, 'exam_id');
    }

    public function candidate()
    {
        return $this->belongsTo(CalonSiswa::class, 'calon_siswa_id');
    }

    public function answers()
    {
        return $this->hasMany(PpdbExamAnswer::class, 'attempt_id');
    }

    public function session()
    {
        return $this->hasOne(PpdbExamSession::class, 'attempt_id');
    }
}
