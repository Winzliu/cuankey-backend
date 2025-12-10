<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Auth\Access\Response;

class WalletPolicy
{
    // aturan yang memastikan bahwa user terkait yang bisa mengakses wallet mereka sendiri

    public function private(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id;
    }
}
