<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Account $account): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditChartOfAccounts);
    }

    public function update(User $user, Account $account): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditChartOfAccounts);
    }

    /**
     * An account that groups other accounts cannot be deleted.
     */
    public function delete(User $user, Account $account): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditChartOfAccounts)
            && ! $account->children()->exists();
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditChartOfAccounts);
    }
}
