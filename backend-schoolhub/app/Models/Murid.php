<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Murid extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'Kelas_id',
        'nis',
        'Nama_lengkap_murid',
        'gambar_murid',
        'gender',
        'tanggal_lahir',
        'alamat',
        'nomor_telepon',
        'nama_orangtua',
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

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
    public function pengumpulanTugas()
    {
    return $this->hasMany(
        PengumpulanTugas::class,
        'murid_id'
    );
    }
}