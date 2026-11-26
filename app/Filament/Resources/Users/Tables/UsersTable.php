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

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('app.users.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('app.users.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('locale')
                    ->label(__('app.users.locale'))
                    ->formatStateUsing(fn (?string $state): ?string => config('app.supported_locales')[$state] ?? $state)
                    ->badge(),
                IconColumn::make('email_verified_at')
                    ->label(__('app.users.email_verified_at'))
                    ->boolean()
                    ->state(fn ($record): bool => $record->email_verified_at !== null)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('app.users.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('name')
            ->recordActions([
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip(fn (DeleteAction $action): string => $action->getLabel())
                    ->modalDescription(__('app.users.remove_help'))
                    ->using(fn (User $record): bool => (bool) $record->companies()->detach(Filament::getTenant())),
                EditAction::make()
                    ->iconButton()
                    ->tooltip(fn (EditAction $action): string => $action->getLabel()),
            ]);
    }
}
