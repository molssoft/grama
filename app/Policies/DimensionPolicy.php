<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Dimension;
use App\Models\User;

class DimensionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Dimension $dimension): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditDimensions);
    }

    public function update(User $user, Dimension $dimension): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditDimensions);
    }

    /**
     * A dimension already used on ledger lines cannot be deleted.
     */
    public function delete(User $user, Dimension $dimension): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditDimensions)
            && ! $dimension->values->contains(fn ($value): bool => $value->isUsed());
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditDimensions);
    }
}
