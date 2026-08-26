<?php

namespace App\Policies;

use App\Models\Campus;
use App\Models\User;

class CampusPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Manager');
    }
    public function view(User $user, Campus $campus): bool
    {
        return $user->hasRole('Manager') && $user->organization_id === $campus->organization_id;
    }
    public function create(User $user): bool
    {
        return $user->hasRole('Manager');
    }
    public function update(User $user, Campus $campus): bool
    {
        return $user->hasRole('Manager') && $user->organization_id === $campus->organization_id;
    }
    public function delete(User $user, Campus $campus): bool
    {
        return $user->hasRole('Manager') && $user->organization_id === $campus->organization_id;
    }
}
