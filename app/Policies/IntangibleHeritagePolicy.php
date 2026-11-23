<?php

namespace App\Policies;

use App\Models\IntangibleHeritage;
use App\Models\User;

class IntangibleHeritagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('heritage.view');
    }

    public function view(User $user, IntangibleHeritage $heritage): bool
    {
        return $user->can('heritage.view');
    }

    public function create(User $user): bool
    {
        return $user->can('heritage.create');
    }

    public function update(User $user, IntangibleHeritage $heritage): bool
    {
        return $user->can('heritage.update');
    }

    public function delete(User $user, IntangibleHeritage $heritage): bool
    {
        return $user->can('heritage.delete');
    }
}
