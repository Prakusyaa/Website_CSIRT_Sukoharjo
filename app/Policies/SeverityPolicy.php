<?php

namespace App\Policies;

use App\Enums\RoleLevel;
use App\Models\Severity;
use App\Models\User;

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
        return $user->hasRoleLevelGreaterThan(RoleLevel::CSIRT->value);
    }

    public function update(User $user, Severity $severity): bool
    {
        return $user->hasRoleLevelGreaterThan(RoleLevel::CSIRT->value);
    }

    public function delete(User $user, Severity $severity): bool
    {
        return $user->hasRoleLevelGreaterThan(RoleLevel::CSIRT->value);
    }
}
