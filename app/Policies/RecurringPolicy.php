<?php

namespace App\Policies;

use App\Models\Recurring;
use App\Models\User;

class RecurringPolicy
{
    public function private(User $user, Recurring $recurring): bool
    {
        return $user->id === $recurring->user_id;
    }
}
