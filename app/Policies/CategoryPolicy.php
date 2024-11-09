<?php

namespace App\Policies;

use App\Models\category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    /**
     * Create a new policy instance.
     */
    public function private(User $user, Category $category): bool
    {
        return $user->id === $category->user_id;
    }
}
