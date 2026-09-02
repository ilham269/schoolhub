<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'nama',
        'kelas',
        'jurusan',
        'angkatan',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function Murid(): HasMany
    {
        return $this->hasMany(Murid::class, 'kelas_id');
    }
}