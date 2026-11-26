<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, User $model): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditUsers);
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditUsers);
    }

    /**
     * Deleting removes the user from the current company only; nobody can remove themselves.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditUsers) && ! $user->is($model);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditUsers);
    }
}
