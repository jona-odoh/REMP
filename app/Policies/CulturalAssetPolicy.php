<?php

namespace App\Policies;

use App\Models\CulturalAsset;
use App\Models\User;

class CulturalAssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('assets.view');
    }

    public function view(User $user, CulturalAsset $asset): bool
    {
        return $user->can('assets.view');
    }

    public function create(User $user): bool
    {
        return $user->can('assets.create');
    }

    public function update(User $user, CulturalAsset $asset): bool
    {
        return $user->can('assets.update');
    }

    public function delete(User $user, CulturalAsset $asset): bool
    {
        return $user->can('assets.delete');
    }
}
