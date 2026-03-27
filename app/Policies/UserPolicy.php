<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->isStaff();
    }

    public function create(User $user): bool
    {
        return false; // Handled by Admin gate override
    }

    public function update(User $user, User $model): bool
    {
        // Users can update their own profile, otherwise requires Admin
        return $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return false;
    }
}
