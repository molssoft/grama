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

namespace App\Filament\Resources\CurrencyRates\Schemas;

use App\Models\Currency;
use App\Models\CurrencyRate;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

class CurrencyRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('currency_id')
                    ->label(__('app.exchange_rates.currency'))
                    ->relationship(
                        'currency',
                        'code',
                        modifyQueryUsing: fn (Builder $query): Builder => $query
                            ->where('is_active', true)
                            ->where('code', '!=', CurrencyRate::PIVOT_CURRENCY_CODE)
                            ->orderBy('code'),
                    )
                    ->getOptionLabelFromRecordUsing(fn (Currency $record): string => $record->code.' '.$record->name)
                    ->searchable(['code', 'name'])
                    ->preload()
                    ->required(),
                DatePicker::make('date')
                    ->label(__('app.exchange_rates.date'))
                    ->required()
                    ->unique(
                        CurrencyRate::class,
                        'date',
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule
                            ->where('company_id', Filament::getTenant()?->getKey())
                            ->where('currency_id', $get('currency_id')),
                    ),
                TextInput::make('rate')
                    ->label(__('app.exchange_rates.rate'))
                    ->helperText(__('app.exchange_rates.rate_help'))
                    ->required()
                    ->numeric()
                    ->minValue(0.00000001)
                    ->maxValue(9999999999)
                    ->rule('decimal:0,8'),
            ]);
    }
}
