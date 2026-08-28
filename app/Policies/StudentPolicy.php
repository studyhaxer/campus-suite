<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin', 'Teacher']);
    }

    public function view(User $user, Student $student): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin', 'Teacher']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin']);
    }

    public function update(User $user, Student $student): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin']);
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->hasRole('Manager');
    }
}