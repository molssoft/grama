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

namespace App\Filament\Resources\Roles\Tables;

use App\Enums\Permission;
use App\Models\Role;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('app.roles.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('permissions')
                    ->label(__('app.roles.permissions'))
                    ->state(fn (Role $record): array => $record->isAdministrator()
                        ? array_map(fn (Permission $permission): string => $permission->getLabel(), Permission::cases())
                        : array_map(fn (string $value): string => Permission::from($value)->getLabel(), $record->permissions ?? []))
                    ->badge(),
            ])
            ->defaultSort('name')
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
