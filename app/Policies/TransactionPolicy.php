<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TransactionPolicy
{
    public function private(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }
}
