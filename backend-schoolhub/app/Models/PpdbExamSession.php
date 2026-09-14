<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpdbExamSession extends Model
{
    protected $fillable = [
        'exam_id',
        'calon_siswa_id',
        'attempt_id',
        'started_at',
        'expires_at',
        'submitted_at',
        'last_activity_at',
        'status',
        'ip_address',
        'user_agent',
        'device_fingerprint',
        'question_order',
        'tab_switches',
        'fullscreen_exits',
        'copy_attempts',
        'paste_attempts',
        'right_click_attempts',
        'suspicion_score',
        'disconnect_count',
        'last_disconnect_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'submitted_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'last_disconnect_at' => 'datetime',
            'question_order' => 'array',
            'tab_switches' => 'integer',
            'fullscreen_exits' => 'integer',
            'copy_attempts' => 'integer',
            'paste_attempts' => 'integer',
            'right_click_attempts' => 'integer',
            'suspicion_score' => 'decimal:2',
            'disconnect_count' => 'integer',
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

    public function attempt()
    {
        return $this->belongsTo(PpdbExamAttempt::class, 'attempt_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(PpdbActivityLog::class, 'session_id');
    }

    /**
     * Check if session is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if session is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    /**
     * Calculate remaining time in seconds
     */
    public function getRemainingSeconds(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return max(0, $this->expires_at->diffInSeconds(now()));
    }

    /**
     * Update suspicion score based on activity
     */
    public function updateSuspicionScore(): void
    {
        $score = 0;

        // Tab switches (5 points each, max 25)
        $score += min($this->tab_switches * 5, 25);

        // Fullscreen exits (10 points each, max 30)
        $score += min($this->fullscreen_exits * 10, 30);

        // Copy attempts (3 points each, max 15)
        $score += min($this->copy_attempts * 3, 15);

        // Paste attempts (3 points each, max 15)
        $score += min($this->paste_attempts * 3, 15);

        // Right click attempts (2 points each, max 10)
        $score += min($this->right_click_attempts * 2, 10);

        // Network disconnects (5 points each, max 15)
        $score += min($this->disconnect_count * 5, 15);

        $this->suspicion_score = min($score, 100);
        $this->save();
    }
}
