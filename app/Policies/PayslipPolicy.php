<?php

namespace App\Policies;

use App\Models\Payslip;
use App\Models\User;

class PayslipPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin']);
    }

    public function view(User $user, Payslip $payslip): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin']);
    }

    public function update(User $user, Payslip $payslip): bool
    {
        if (! $user->hasAnyRole(['Manager', 'Campus Admin'])) {
            return false;
        }

        // Campus Admins can process payroll for Teacher/Accountant staff only —
        // never another admin or the Manager account.
        if ($user->hasRole('Campus Admin') && $payslip->staff->hasAnyRole(['Manager', 'Campus Admin'])) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Payslip $payslip): bool
    {
        return $user->hasRole('Manager');
    }
}