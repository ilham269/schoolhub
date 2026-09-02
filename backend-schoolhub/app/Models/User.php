<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{



    use HasApiTokens, Notifiable;
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
             'is_active' => 'boolean',
        ];
    }

    //relasi 
    public function Murid()
    {
        return $this->hasOne(Murid::class);
    }

    public function Guru()
    {
        return $this->hasOne(Guru::class);
    }

    public function Karyawan()
    {
        return $this->hasOne(Karyawan::class);
    }
    public function announcements()
    {
    return $this->hasMany(
        Announcement::class,
        'created_by'
    );
    }

    public function news()
    {
    return $this->hasMany(
        News::class,
        'created_by'
    );
    }
}
