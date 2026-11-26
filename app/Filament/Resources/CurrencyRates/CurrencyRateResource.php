<?php

/**
 * Grama - open source accounting software.
 * Copyright (C) 2026 SIA Molssoft Latvija
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

namespace App\Filament\Resources\CurrencyRates;

use App\Filament\Resources\CurrencyRates\Pages\ManageCurrencyRates;
use App\Filament\Resources\CurrencyRates\Schemas\CurrencyRateForm;
use App\Filament\Resources\CurrencyRates\Tables\CurrencyRatesTable;
use App\Models\CurrencyRate;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CurrencyRateResource extends Resource
{
    protected static ?string $model = CurrencyRate::class;

    /**
     * Global rates have no company, so rows are scoped by hand in getEloquentQuery().
     */
    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation.currencies');
    }

    public static function getModelLabel(): string
    {
        return __('app.exchange_rates.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.exchange_rates.plural');
    }

    /**
     * The global rates plus the current company's own custom rates, never another company's.
     *
     * @return Builder<CurrencyRate>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('currency')
            ->where(fn (Builder $query): Builder => $query
                ->whereNull('company_id')
                ->orWhere('company_id', Filament::getTenant()?->getKey()));
    }

    public static function form(Schema $schema): Schema
    {
        return CurrencyRateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CurrencyRatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCurrencyRates::route('/'),
        ];
    }
}
