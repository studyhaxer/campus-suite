<?php

namespace App\Policies;

use App\Models\FeeInvoice;
use App\Models\User;

class FeeInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin', 'Accountant']);
    }

    public function view(User $user, FeeInvoice $invoice): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin', 'Accountant']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin', 'Accountant']);
    }

    public function update(User $user, FeeInvoice $invoice): bool
    {
        return $user->hasAnyRole(['Manager', 'Campus Admin', 'Accountant']);
    }

    public function delete(User $user, FeeInvoice $invoice): bool
    {
        return $user->hasRole('Manager');
    }
}