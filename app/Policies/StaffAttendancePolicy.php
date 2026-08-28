<?php

namespace App\Policies;

use App\Models\StaffAttendance;
use App\Models\User;

class StaffAttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin']);
    }

    public function view(User $user, StaffAttendance $attendance): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin']);
    }

    public function update(User $user, StaffAttendance $attendance): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin']);
    }

    public function delete(User $user, StaffAttendance $attendance): bool
    {
        return $user->hasRole('Manager');
    }
}