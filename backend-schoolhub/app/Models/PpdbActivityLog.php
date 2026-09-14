<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpdbActivityLog extends Model
{
    const UPDATED_AT = null; // Only has created_at

    protected $fillable = [
        'session_id',
        'event',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function session()
    {
        return $this->belongsTo(PpdbExamSession::class, 'session_id');
    }

    /**
     * Log an event
     */
    public static function logEvent(int $sessionId, string $event, ?string $description = null, ?array $metadata = null): void
    {
        self::create([
            'session_id' => $sessionId,
            'event' => $event,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
