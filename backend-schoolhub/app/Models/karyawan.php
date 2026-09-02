<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawans';

    protected $fillable = [
        'user_id',
        'nip',
        'nama_lengkap_karyawan',
        'bagian',
        'nomor_telepon',
        'alamat',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}