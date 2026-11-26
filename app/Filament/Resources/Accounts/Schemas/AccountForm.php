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

namespace App\Filament\Resources\Accounts\Schemas;

use App\Enums\AccountType;
use App\Models\Account;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label(__('app.accounts.code'))
                    ->helperText(__('app.accounts.code_help'))
                    ->required()
                    ->maxLength(32)
                    ->unique(
                        Account::class,
                        'code',
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule): Unique => $rule->where('company_id', Filament::getTenant()?->getKey()),
                    ),
                TextInput::make('name')
                    ->label(__('app.accounts.name'))
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label(__('app.accounts.type'))
                    ->options(AccountType::class)
                    ->required()
                    ->helperText(fn (Get $get): ?string => filled($get('parent_id')) ? __('app.accounts.type_follows_parent') : null)
                    ->disabled(fn (Get $get, ?Account $record): bool => filled($get('parent_id')) || ($record?->children()->exists() ?? false))
                    ->dehydrated(),
                Select::make('parent_id')
                    ->label(__('app.accounts.parent'))
                    ->helperText(__('app.accounts.parent_help'))
                    ->relationship(
                        'parent',
                        'name',
                        modifyQueryUsing: fn (Builder $query, ?Account $record): Builder => $query
                            ->whereBelongsTo(Filament::getTenant())
                            ->where('is_postable', false)
                            ->when($record, fn (Builder $query, Account $record): Builder => $query
                                ->whereKeyNot([$record->getKey(), ...$record->descendantIds()])
                                // The type of an account with children is fixed, and children must match their parent.
                                ->when($record->children()->exists(), fn (Builder $query): Builder => $query->where('type', $record->type)))
                            ->orderBy('code'),
                    )
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state, mixed $old, ?Account $record): void {
                        $parent = filled($state) ? Account::query()->find($state) : null;

                        if ($parent === null) {
                            return;
                        }

                        $set('type', $parent->type->value);

                        // Suggest a code on new accounts, unless the user already typed their own.
                        $previousSuggestion = filled($old) ? Account::query()->find($old)?->suggestChildCode() : null;

                        if ($record === null && (blank($get('code')) || $get('code') === $previousSuggestion)) {
                            $set('code', $parent->suggestChildCode());
                        }
                    })
                    ->getOptionLabelFromRecordUsing(fn (Account $record): string => $record->display_name)
                    ->searchable(['code', 'name'])
                    ->preload(),
                Toggle::make('is_postable')
                    ->label(__('app.accounts.is_postable'))
                    ->helperText(fn (?Account $record): string => ($record?->children()->exists() ?? false)
                        ? __('app.accounts.is_postable_locked')
                        : __('app.accounts.is_postable_help'))
                    ->default(true)
                    ->disabled(fn (?Account $record): bool => $record?->children()->exists() ?? false)
                    ->dehydrated(fn (?Account $record): bool => ! ($record?->children()->exists() ?? false)),
                Toggle::make('is_active')
                    ->label(__('app.accounts.is_active'))
                    ->default(true),
            ]);
    }
}
