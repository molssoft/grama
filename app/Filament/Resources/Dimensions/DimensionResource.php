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

namespace App\Filament\Resources\Dimensions;

use App\Filament\Resources\Dimensions\Pages\CreateDimension;
use App\Filament\Resources\Dimensions\Pages\EditDimension;
use App\Filament\Resources\Dimensions\Pages\ListDimensions;
use App\Filament\Resources\Dimensions\RelationManagers\ValuesRelationManager;
use App\Filament\Resources\Dimensions\Schemas\DimensionForm;
use App\Filament\Resources\Dimensions\Tables\DimensionsTable;
use App\Models\Dimension;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DimensionResource extends Resource
{
    protected static ?string $model = Dimension::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation.accounting');
    }

    public static function getModelLabel(): string
    {
        return __('app.dimensions.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.dimensions.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return DimensionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DimensionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ValuesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDimensions::route('/'),
            'create' => CreateDimension::route('/create'),
            'edit' => EditDimension::route('/{record}/edit'),
        ];
    }
}
