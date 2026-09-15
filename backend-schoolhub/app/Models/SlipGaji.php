<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlipGaji extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'slip_gajis';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'karyawan_id',
        'periode',
        'gaji_pokok',
        'tunjangan',
        'bonus',
        'potongan',
        'total_gaji',
        'status',
        'slip_number',
        'file_path',
        'catatan',
        'dibuat_oleh',
        'approved_oleh',
        'approved_at',
        'paid_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'periode' => 'date',
            'gaji_pokok' => 'decimal:2',
            'tunjangan' => 'decimal:2',
            'bonus' => 'decimal:2',
            'potongan' => 'decimal:2',
            'total_gaji' => 'decimal:2',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the karyawan that owns the slip gaji.
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    /**
     * Get the user who created the slip gaji.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /**
     * Get the user who approved the slip gaji.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_oleh');
    }

    /**
     * Scope untuk filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter by periode.
     */
    public function scopePeriode($query, string $periode)
    {
        return $query->where('periode', $periode);
    }

    /**
     * Check if slip is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }

    /**
     * Check if slip is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }

    /**
     * Check if slip is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'PAID';
    }

    /**
     * Calculate total gaji.
     */
    public function calculateTotal(): float
    {
        return (float) ($this->gaji_pokok + $this->tunjangan + $this->bonus - $this->potongan);
    }
}
