<?php

namespace App\Support;

use App\Models\User;

class AcademicAccess
{
    public static function role(?User $user): string
    {
        return strtolower((string) ($user?->role ?? ''));
    }

    public static function isAdmin(?User $user): bool
    {
        return self::role($user) === 'admin';
    }

    public static function isGuru(?User $user): bool
    {
        return self::role($user) === 'guru';
    }

    public static function isMurid(?User $user): bool
    {
        return self::role($user) === 'murid';
    }

    public static function guruId(?User $user): ?int
    {
        return $user?->guru?->id;
    }

    public static function kelasId(?User $user): ?int
    {
        return $user?->murid?->kelas_id;
    }
}
