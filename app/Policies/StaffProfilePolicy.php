<?php

namespace App\Policies;

use App\Models\StaffProfile;
use App\Models\User;

class StaffProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin']);
    }

    public function view(User $user, StaffProfile $profile): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin']);
    }

    public function update(User $user, StaffProfile $profile): bool
    {
        if (! $user->hasAnyRole(['Manager', 'Campus Admin'])) {
            return false;
        }

        // Campus Admins can manage Teacher/Accountant staff only —
        // never another admin or the Manager account.
        if ($user->hasRole('Campus Admin') && $profile->user->hasAnyRole(['Manager', 'Campus Admin'])) {
            return false;
        }

        return true;
    }

    public function delete(User $user, StaffProfile $profile): bool
    {
        return $user->hasRole('Manager');
    }
}