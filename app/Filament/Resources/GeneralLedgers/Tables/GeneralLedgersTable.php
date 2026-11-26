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

namespace App\Filament\Resources\GeneralLedgers\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GeneralLedgersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label(__('app.general_ledger.number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label(__('app.general_ledger.date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('reference')
                    ->label(__('app.general_ledger.reference'))
                    ->searchable(),
                TextColumn::make('description')
                    ->label(__('app.general_ledger.description'))
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('lines_sum_debit')
                    ->label(__('app.general_ledger.total'))
                    ->sum('lines', 'debit')
                    ->numeric(2),
            ])
            ->defaultSort('date', 'desc')
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
