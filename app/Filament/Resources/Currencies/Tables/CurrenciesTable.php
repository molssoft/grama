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

namespace App\Filament\Resources\Currencies\Tables;

use App\Models\Currency;
use App\Models\CurrencyRate;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CurrenciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->addSelect([
                'latest_rate' => CurrencyRate::query()->select('rate')->whereColumn('currency_id', 'currencies.id')->whereNull('company_id')->latest('date')->limit(1),
                'latest_rate_date' => CurrencyRate::query()->select('date')->whereColumn('currency_id', 'currencies.id')->whereNull('company_id')->latest('date')->limit(1),
            ]))
            ->columns([
                TextColumn::make('code')
                    ->label(__('app.currencies.code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('app.currencies.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('symbol')
                    ->label(__('app.currencies.symbol')),
                TextColumn::make('decimal_places')
                    ->label(__('app.currencies.decimal_places'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('latest_rate')
                    ->label(__('app.currencies.latest_rate'))
                    ->tooltip(__('app.currencies.latest_rate_help'))
                    ->state(fn (Currency $record): ?string => $record->latest_rate === null ? null : rtrim(rtrim((string) $record->latest_rate, '0'), '.'))
                    ->sortable(),
                TextColumn::make('latest_rate_date')
                    ->label(__('app.currencies.latest_rate_date'))
                    ->date(__('app.currencies.date_format'))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('app.currencies.is_active'))
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('app.currencies.is_active')),
            ])
            ->defaultSort('code')
            ->paginated([25, 50, 100]);
    }
}
