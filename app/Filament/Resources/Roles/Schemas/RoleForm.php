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

namespace App\Filament\Resources\Roles\Schemas;

use App\Enums\Permission;
use App\Models\Role;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('app.roles.name'))
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn (?Role $record): bool => $record?->isAdministrator() ?? false)
                    ->unique(
                        Role::class,
                        'name',
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule): Unique => $rule->where('company_id', Filament::getTenant()?->getKey()),
                    ),
                CheckboxList::make('permissions')
                    ->label(__('app.roles.permissions'))
                    ->options(Permission::class)
                    ->helperText(fn (?Role $record): ?string => $record?->isAdministrator() ? __('app.roles.administrator_help') : null)
                    ->disabled(fn (?Role $record): bool => $record?->isAdministrator() ?? false)
                    ->dehydrated(fn (?Role $record): bool => ! ($record?->isAdministrator() ?? false)),
            ]);
    }
}
