<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\GeneralLedger;
use App\Models\User;

class GeneralLedgerPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, GeneralLedger $generalLedger): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditGeneralLedger);
    }

    public function update(User $user, GeneralLedger $generalLedger): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditGeneralLedger);
    }

    public function delete(User $user, GeneralLedger $generalLedger): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditGeneralLedger);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditGeneralLedger);
    }
}
