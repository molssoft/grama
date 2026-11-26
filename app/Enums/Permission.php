<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Permission: string implements HasLabel
{
    case EditChartOfAccounts = 'edit_chart_of_accounts';
    case EditCompanyData = 'edit_company_data';
    case EditDimensions = 'edit_dimensions';
    case EditExchangeRates = 'edit_exchange_rates';
    case EditGeneralLedger = 'edit_general_ledger';
    case EditRoles = 'edit_roles';
    case EditUsers = 'edit_users';

    public function getLabel(): string
    {
        return __('app.permissions.'.$this->value);
    }
}
