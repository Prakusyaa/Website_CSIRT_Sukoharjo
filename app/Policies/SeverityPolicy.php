<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Severity;

class SeverityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Severity $severity): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Severity $severity): bool
    {
        return false;
    }

    public function delete(User $user, Severity $severity): bool
    {
        return false;
    }
}
