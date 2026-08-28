<?php

namespace App\Policies;

use App\Models\FeePayment;
use App\Models\User;

class FeePaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin', 'Accountant']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin', 'Accountant']);
    }

    public function delete(User $user, FeePayment $payment): bool
    {
        return $user->hasRole('Manager');
    }
}