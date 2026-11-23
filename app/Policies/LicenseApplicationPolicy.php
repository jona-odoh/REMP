<?php

namespace App\Policies;

use App\Models\LicenseApplication;
use App\Models\User;

class LicenseApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('licenses.view');
    }

    public function view(User $user, LicenseApplication $application): bool
    {
        return $user->can('licenses.view');
    }

    public function create(User $user): bool
    {
        return $user->can('licenses.create');
    }

    public function review(User $user, LicenseApplication $application): bool
    {
        return $user->can('licenses.review');
    }

    public function approve(User $user, LicenseApplication $application): bool
    {
        return $user->can('licenses.approve');
    }
}
