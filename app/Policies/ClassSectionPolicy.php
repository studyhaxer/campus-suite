<?php

namespace App\Policies;

use App\Models\ClassSection;
use App\Models\User;

class ClassSectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin', 'Teacher']);
    }

    public function view(User $user, ClassSection $classSection): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin', 'Teacher']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin']);
    }

    public function update(User $user, ClassSection $classSection): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin']);
    }

    public function delete(User $user, ClassSection $classSection): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin']);
    }
}