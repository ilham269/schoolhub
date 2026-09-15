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
        'kelas_id',
        'nis',
        'gambar_murid',
        'gender',
        'tanggal_lahir',
        'tempat_lahir',
        'alamat',
        'nomor_telepon',
        'nama_orangtua',
        'nama_ayah',
        'nama_ibu',
        'pekerjaan_ayah',
        'pekerjaan_ibu',
        'nomor_telepon_ortu',
        'agama',
        'anak_ke',
        'jumlah_saudara',
        'hobi',
        'cita_cita',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */

    // Accessor untuk nama_lengkap_murid (lowercase)
    public function getNamaLengkapMuridAttribute()
    {
        return $this->attributes['Nama_lengkap_murid'] ?? null;
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