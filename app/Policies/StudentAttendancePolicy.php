<?php

namespace App\Policies;

use App\Models\StudentAttendance;
use App\Models\User;

class StudentAttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin', 'Teacher']);
    }

    public function view(User $user, StudentAttendance $attendance): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin', 'Teacher']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin', 'Teacher']);
    }

    public function update(User $user, StudentAttendance $attendance): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin', 'Teacher']);
    }

    public function delete(User $user, StudentAttendance $attendance): bool
    {
        return $user->hasRole('Manager');
    }
}