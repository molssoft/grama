<?php

use App\Models\Account;
use App\Models\Company;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Give every existing company the default chart of accounts.
     */
    public function up(): void
    {
        Company::query()->each(function (Company $company): void {
            if (! $company->accounts()->exists()) {
                Account::createDefaultChart($company);
            }
        });
    }

    /**
     * The accounts table is dropped by its own migration; nothing to reverse here.
     */
    public function down(): void {}
};
