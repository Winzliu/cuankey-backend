<?php

namespace App\Policies;

use App\Models\Recurring;
use App\Models\User;

class RecurringPolicy
{
    // aturan yang memastikan bahwa hanya user terkait yang bisa mengakses transaksi perulangan mereka sendiri

    public function private(User $user, Recurring $recurring): bool
    {
        return $user->id === $recurring->user_id;
    }
}
