<?php

namespace App\Providers;

use App\Http\Controllers\CompanyRedirectController;
use App\Models\Company;
use Filament\Facades\Filament;
use Filament\Http\Controllers\RedirectToTenantController;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RedirectToTenantController::class, CompanyRedirectController::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $dateFormat = fn (): string => Filament::getTenant()?->date_format ?? Company::DEFAULT_DATE_FORMAT;
        $dateTimeFormat = fn (): string => Filament::getTenant()?->datetime_format ?? Company::DEFAULT_DATETIME_FORMAT;

        Table::configureUsing(fn (Table $table): Table => $table
            ->defaultDateDisplayFormat($dateFormat)
            ->defaultDateTimeDisplayFormat($dateTimeFormat));

        Schema::configureUsing(fn (Schema $schema): Schema => $schema
            ->defaultDateDisplayFormat($dateFormat)
            ->defaultDateTimeDisplayFormat($dateTimeFormat));
    }
}
