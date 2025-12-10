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
    // aturan yang memastikan bahwa user terkait yang bisa mengakses kategori mereka sendiri
    public function private(User $user, Category $category): bool
    {
        return $user->id === $category->user_id;
    }
}
