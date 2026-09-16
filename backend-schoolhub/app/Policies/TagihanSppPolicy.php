<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TagihanSpp;
use App\Models\User;

class TagihanSppPolicy
{
    public function bayar(User $user, TagihanSpp $tagihan): bool
    {
        return $user->murid !== null && (int) $user->murid->id === (int) $tagihan->murid_id;
    }

    public function download(User $user, TagihanSpp $tagihan): bool
    {
        return $this->bayar($user, $tagihan);
    }
}
