<?php

namespace App\Policies;

use App\Models\Inspection;
use App\Models\User;

class InspectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('inspections.view');
    }

    public function view(User $user, Inspection $inspection): bool
    {
        return $user->can('inspections.view');
    }

    public function create(User $user): bool
    {
        return $user->can('inspections.create');
    }

    public function update(User $user, Inspection $inspection): bool
    {
        return $user->can('inspections.update');
    }

    public function delete(User $user, Inspection $inspection): bool
    {
        return $user->can('inspections.delete');
    }
}
