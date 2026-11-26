<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Role $role): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditRoles);
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditRoles);
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditRoles)
            && ! $role->isAdministrator()
            && ! $role->users()->exists();
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditRoles);
    }
}
