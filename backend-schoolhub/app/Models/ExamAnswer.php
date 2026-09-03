<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAnswer extends Model
{
    protected $table = 'exam_answers';

    protected $fillable = [
        'exam_attempt_id',
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

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(
            ExamAttempt::class,
            'exam_attempt_id'
        );
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(
            Question::class,
            'question_id'
        );
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(
            Option::class,
            'option_id'
        );
    }
}