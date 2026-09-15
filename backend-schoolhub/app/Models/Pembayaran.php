<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pembayaran extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'pembayarans';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tagihan_id',
        'gateway',
        'transaction_id',
        'snap_token',
        'payment_type',
        'va_number',
        'bank',
        'gross_amount',
        'status',
        'paid_at',
        'expired_at',
        'raw_callback',
        'signature_verified',
        'callback_received_at',
        'processed_by_job_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'gross_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
            'callback_received_at' => 'datetime',
            'processed_by_job_at' => 'datetime',
            'raw_callback' => 'array',
            'signature_verified' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the tagihan that owns the pembayaran.
     */
    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(TagihanSpp::class, 'tagihan_id');
    }

    /**
     * Get the payment gateway logs for the pembayaran.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(PaymentGatewayLog::class, 'pembayaran_id');
    }

    /**
     * Scope untuk filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter by gateway.
     */
    public function scopeGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Check if pembayaran berhasil.
     */
    public function isSuccess(): bool
    {
        return $this->status === 'SUCCESS';
    }

    /**
     * Check if pembayaran pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    /**
     * Check if pembayaran expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'EXPIRED' || 
               ($this->expired_at && now()->isAfter($this->expired_at));
    }
}
