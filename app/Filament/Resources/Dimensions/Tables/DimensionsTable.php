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

namespace App\Filament\Resources\Dimensions\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DimensionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('app.dimensions.code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('app.dimensions.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('values_count')
                    ->label(__('app.dimensions.values'))
                    ->counts('values'),
            ])
            ->defaultSort('code')
            ->recordActions([
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip(fn (DeleteAction $action): string => $action->getLabel()),
                EditAction::make()
                    ->iconButton()
                    ->tooltip(fn (EditAction $action): string => $action->getLabel()),
            ]);
    }
}
