<?php

namespace App\Policies;

use App\Models\User;

class SystemConfigPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('config.view');
    }

    public function view(User $user): bool
    {
        return $user->can('config.view');
    }

    public function create(User $user): bool
    {
        return $user->can('config.manage');
    }

    public function update(User $user): bool
    {
        return $user->can('config.manage');
    }

    public function delete(User $user): bool
    {
        return $user->can('config.manage');
    }
}
