<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentGatewayLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'payment_gateway_logs';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'pembayaran_id',
        'gateway',
        'action',
        'request_payload',
        'response_payload',
        'http_status',
        'error_message',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
            'http_status' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the pembayaran that owns the log.
     */
    public function pembayaran(): BelongsTo
    {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_id');
    }

    /**
     * Scope untuk filter by gateway.
     */
    public function scopeGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Scope untuk filter by action.
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Check if log is error.
     */
    public function isError(): bool
    {
        return $this->http_status >= 400 || !empty($this->error_message);
    }

    /**
     * Check if log is success.
     */
    public function isSuccess(): bool
    {
        return $this->http_status >= 200 && $this->http_status < 300;
    }
}
