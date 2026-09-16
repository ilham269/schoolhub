<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TagihanSpp extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'tagihan_spps';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'murid_id',
        'periode',
        'jumlah',
        'denda',
        'total',
        'jatuh_tempo',
        'status',
        'invoice_number',
        'kuitansi_path',
        'paid_at',
        'notes',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'periode' => 'date',
            'jumlah' => 'decimal:2',
            'denda' => 'decimal:2',
            'total' => 'decimal:2',
            'jatuh_tempo' => 'date',
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the murid that owns the tagihan.
     */
    public function murid(): BelongsTo
    {
        return $this->belongsTo(Murid::class, 'murid_id');
    }

    /**
     * Get the user who created the tagihan.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the pembayarans for the tagihan.
     */
    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'tagihan_id');
    }

    /**
     * Get the successful payment for this tagihan.
     */
    public function successfulPayment(): ?Pembayaran
    {
        return $this->pembayarans()
            ->where('status', 'SUCCESS')
            ->first();
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
     * Check if tagihan sudah lunas.
     */
    public function isLunas(): bool
    {
        return $this->status === 'LUNAS';
    }

    /**
     * Check if tagihan sudah jatuh tempo.
     */
    public function isOverdue(): bool
    {
        return now()->isAfter($this->jatuh_tempo) && !$this->isLunas();
    }
}
