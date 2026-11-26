<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\CurrencyRate;
use App\Models\User;
use Filament\Facades\Filament;

class CurrencyRatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CurrencyRate $currencyRate): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditExchangeRates);
    }

    /**
     * Global rates are read only: a company can change only its own custom rates.
     */
    public function update(User $user, CurrencyRate $currencyRate): bool
    {
        return $this->ownsCustomRate($user, $currencyRate);
    }

    public function delete(User $user, CurrencyRate $currencyRate): bool
    {
        return $this->ownsCustomRate($user, $currencyRate);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionInCurrentCompany(Permission::EditExchangeRates);
    }

    private function ownsCustomRate(User $user, CurrencyRate $currencyRate): bool
    {
        return $currencyRate->company_id !== null
            && $currencyRate->company_id === Filament::getTenant()?->getKey()
            && $user->hasPermissionInCurrentCompany(Permission::EditExchangeRates);
    }
}
