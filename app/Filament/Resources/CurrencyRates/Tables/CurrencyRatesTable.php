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

namespace App\Filament\Resources\CurrencyRates\Tables;

use App\Models\Currency;
use App\Models\CurrencyRate;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CurrencyRatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label(__('app.exchange_rates.date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('currency.code')
                    ->label(__('app.exchange_rates.currency'))
                    ->description(fn (CurrencyRate $record): string => $record->currency->name)
                    ->searchable(['currencies.code', 'currencies.name']),
                TextColumn::make('rate')
                    ->label(__('app.exchange_rates.rate'))
                    ->tooltip(__('app.exchange_rates.rate_help'))
                    ->state(fn (CurrencyRate $record): string => self::formatRate($record->rate))
                    ->sortable(),
                TextColumn::make('company_id')
                    ->label(__('app.exchange_rates.type'))
                    ->state(fn (CurrencyRate $record): string => $record->company_id === null
                        ? __('app.exchange_rates.global')
                        : __('app.exchange_rates.custom'))
                    ->badge()
                    ->color(fn (CurrencyRate $record): string => $record->company_id === null ? 'gray' : 'primary'),
                TextColumn::make('source')
                    ->label(__('app.exchange_rates.source'))
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
            ])
            ->filters([
                SelectFilter::make('currency_id')
                    ->label(__('app.exchange_rates.currency'))
                    ->relationship('currency', 'code', fn (Builder $query): Builder => $query->orderBy('code'))
                    ->getOptionLabelFromRecordUsing(fn (Currency $record): string => $record->code.' '.$record->name)
                    ->searchable(['code', 'name'])
                    ->preload()
                    ->multiple(),
                Filter::make('date')
                    ->label(__('app.exchange_rates.date'))
                    ->schema([
                        DatePicker::make('from')->label(__('app.exchange_rates.from')),
                        DatePicker::make('until')->label(__('app.exchange_rates.until')),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('date', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('date', '<=', $date)))
                    ->indicateUsing(function (array $data): array {
                        return array_values(array_filter([
                            ($data['from'] ?? null) ? __('app.exchange_rates.from').': '.$data['from'] : null,
                            ($data['until'] ?? null) ? __('app.exchange_rates.until').': '.$data['until'] : null,
                        ]));
                    }),
                SelectFilter::make('type')
                    ->label(__('app.exchange_rates.type'))
                    ->options([
                        'global' => __('app.exchange_rates.global'),
                        'custom' => __('app.exchange_rates.custom'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'global' => $query->whereNull('company_id'),
                        'custom' => $query->whereNotNull('company_id'),
                        default => $query,
                    }),
            ])
            ->defaultSort('date', 'desc')
            ->recordActions([
                Action::make('setCustomRate')
                    ->label(__('app.exchange_rates.set_custom'))
                    ->icon('heroicon-o-pencil-square')
                    ->iconButton()
                    ->tooltip(__('app.exchange_rates.set_custom'))
                    ->visible(fn (CurrencyRate $record): bool => $record->company_id === null
                        && auth()->user()?->can('create', CurrencyRate::class))
                    ->modalDescription(__('app.exchange_rates.custom_help'))
                    ->fillForm(fn (CurrencyRate $record): array => ['rate' => self::formatRate($record->rate)])
                    ->schema([
                        TextInput::make('rate')
                            ->label(__('app.exchange_rates.rate'))
                            ->helperText(__('app.exchange_rates.rate_help'))
                            ->required()
                            ->numeric()
                            ->minValue(0.00000001)
                            ->maxValue(9999999999)
                            ->rule('decimal:0,8'),
                    ])
                    ->action(fn (CurrencyRate $record, array $data) => CurrencyRate::query()->updateOrCreate(
                        [
                            'company_id' => Filament::getTenant()?->getKey(),
                            'currency_id' => $record->currency_id,
                            'date' => $record->date->toDateString(),
                        ],
                        ['rate' => $data['rate'], 'source' => 'manual'],
                    )),
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip(fn (DeleteAction $action): string => $action->getLabel()),
                EditAction::make()
                    ->iconButton()
                    ->tooltip(fn (EditAction $action): string => $action->getLabel()),
            ]);
    }

    /**
     * Shows a stored rate without trailing zeros, but with at least four decimals.
     */
    private static function formatRate(string $rate): string
    {
        return preg_replace('/(\.\d{4}\d*?)0+$/', '$1', $rate);
    }
}
