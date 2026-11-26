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

namespace App\Filament\Resources\GeneralLedgers\Schemas;

use App\Models\Account;
use App\Models\Dimension;
use App\Models\DimensionValue;
use App\Models\GeneralLedger;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class GeneralLedgerForm
{
    public static function configure(Schema $schema): Schema
    {
        $dimensions = Dimension::query()
            ->whereBelongsTo(Filament::getTenant())
            ->with('values')
            ->orderBy('code')
            ->get();

        return $schema
            ->components([
                TextInput::make('number')
                    ->label(__('app.general_ledger.number'))
                    ->required()
                    ->maxLength(32)
                    ->default(fn (): string => (string) (GeneralLedger::query()->whereBelongsTo(Filament::getTenant())->count() + 1))
                    ->unique(
                        GeneralLedger::class,
                        'number',
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule): Unique => $rule->where('company_id', Filament::getTenant()?->getKey()),
                    ),
                DatePicker::make('date')
                    ->label(__('app.general_ledger.date'))
                    ->required()
                    ->default(now()),
                TextInput::make('reference')
                    ->label(__('app.general_ledger.reference'))
                    ->maxLength(255),
                Textarea::make('description')
                    ->label(__('app.general_ledger.description'))
                    ->columnSpanFull(),
                Repeater::make('rows')
                    ->label(__('app.general_ledger.lines'))
                    ->columnSpanFull()
                    ->minItems(1)
                    ->defaultItems(1)
                    ->table([
                        Repeater\TableColumn::make(__('app.general_ledger.debit_account')),
                        Repeater\TableColumn::make(__('app.general_ledger.credit_account')),
                        Repeater\TableColumn::make(__('app.general_ledger.line_description')),
                        Repeater\TableColumn::make(__('app.general_ledger.amount')),
                        ...$dimensions->map(fn (Dimension $dimension): Repeater\TableColumn => Repeater\TableColumn::make($dimension->name))->all(),
                    ])
                    ->schema([
                        self::accountSelect('debit_account_id', __('app.general_ledger.debit_account')),
                        self::accountSelect('credit_account_id', __('app.general_ledger.credit_account'))
                            ->different('debit_account_id'),
                        TextInput::make('description')
                            ->label(__('app.general_ledger.line_description'))
                            ->maxLength(255),
                        TextInput::make('amount')
                            ->label(__('app.general_ledger.amount'))
                            ->numeric()
                            ->minValue(0.01)
                            ->required(),
                        ...$dimensions->map(fn (Dimension $dimension): Select => Select::make('dimensions.'.$dimension->getKey())
                            ->label($dimension->name)
                            ->options($dimension->values->mapWithKeys(fn (DimensionValue $value): array => [$value->getKey() => $value->display_name])->all())
                            ->searchable())->all(),
                    ]),
            ]);
    }

    /**
     * Select of the company's active accounts that can be posted to.
     */
    private static function accountSelect(string $name, string $label): Select
    {
        return Select::make($name)
            ->label($label)
            ->options(fn (): array => Account::query()
                ->whereBelongsTo(Filament::getTenant())
                ->where('is_postable', true)
                ->where('is_active', true)
                ->orderBy('code')
                ->get()
                ->mapWithKeys(fn (Account $account): array => [$account->getKey() => $account->display_name])
                ->all())
            ->searchable()
            ->required();
    }
}
