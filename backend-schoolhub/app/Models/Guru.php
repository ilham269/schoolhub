<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guru extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nip',
        'gamabar_guru',
        'gender',
        'tanggal_lahir',
        'alamat',
        'nomor_telepon',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function subjekgurus()
    {
    return $this->hasMany(Subjekguru::class, 'guru_id');
    }
    public function jadwals()
    {
    return $this->hasMany(Jadwal::class, 'guru_id');
    }
}