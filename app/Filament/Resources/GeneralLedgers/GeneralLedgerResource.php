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

namespace App\Filament\Resources\GeneralLedgers;

use App\Filament\Resources\GeneralLedgers\Pages\CreateGeneralLedger;
use App\Filament\Resources\GeneralLedgers\Pages\EditGeneralLedger;
use App\Filament\Resources\GeneralLedgers\Pages\ListGeneralLedgers;
use App\Filament\Resources\GeneralLedgers\Schemas\GeneralLedgerForm;
use App\Filament\Resources\GeneralLedgers\Tables\GeneralLedgersTable;
use App\Models\GeneralLedger;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GeneralLedgerResource extends Resource
{
    protected static ?string $model = GeneralLedger::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'number';

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation.accounting');
    }

    public static function getModelLabel(): string
    {
        return __('app.general_ledger.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.general_ledger.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return GeneralLedgerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GeneralLedgersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGeneralLedgers::route('/'),
            'create' => CreateGeneralLedger::route('/create'),
            'edit' => EditGeneralLedger::route('/{record}/edit'),
        ];
    }
}
