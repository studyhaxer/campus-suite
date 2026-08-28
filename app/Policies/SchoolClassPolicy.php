<?php

namespace App\Policies;

use App\Models\SchoolClass;
use App\Models\User;

class SchoolClassPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin', 'Teacher']);
    }

    public function view(User $user, SchoolClass $schoolClass): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin', 'Teacher']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin']);
    }

    public function update(User $user, SchoolClass $schoolClass): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin']);
    }

    public function delete(User $user, SchoolClass $schoolClass): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin']);
    }
}