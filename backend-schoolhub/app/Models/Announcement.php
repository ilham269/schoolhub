<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $table = 'announcements';

    protected $fillable = [
        'title',
        'content',
        'image',
        'published_at',
        'expires_at',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }
    public function creator(): BelongsTo
    {
    return $this->belongsTo(User::class, 'created_by');
    }
}