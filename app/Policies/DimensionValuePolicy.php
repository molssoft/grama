<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\DimensionValue;
use App\Models\User;

class DimensionValuePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DimensionValue $dimensionValue): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditDimensions);
    }

    public function update(User $user, DimensionValue $dimensionValue): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditDimensions);
    }

    /**
     * A value already used on ledger lines cannot be deleted.
     */
    public function delete(User $user, DimensionValue $dimensionValue): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditDimensions)
            && ! $dimensionValue->isUsed();
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditDimensions);
    }
}
