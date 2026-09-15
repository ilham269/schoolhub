<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpdbExamAnswer extends Model
{
    protected $fillable = [
        'attempt_id',
        'question_id',
        'option_id',
        'answer_text',
        'is_correct',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'score' => 'decimal:2',
        ];
    }

    public function attempt()
    {
        return $this->belongsTo(PpdbExamAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(PpdbQuestion::class, 'question_id');
    }

    public function option()
    {
        return $this->belongsTo(PpdbOption::class, 'option_id');
    }
}
