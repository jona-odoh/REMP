<?php

namespace App\Policies;

use App\Models\LicenseFee;
use App\Models\User;

class LicenseFeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('fees.view');
    }

    public function create(User $user): bool
    {
        return $user->can('fees.create');
    }

    public function delete(User $user, LicenseFee $fee): bool
    {
        return $user->can('fees.delete');
    }
}
