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

namespace App\Filament\Resources\Dimensions\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;

class ValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'values';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('app.dimensions.values');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label(__('app.dimensions.code'))
                    ->required()
                    ->maxLength(32)
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule): Unique => $rule->where('dimension_id', $this->getOwnerRecord()->getKey()),
                    ),
                TextInput::make('name')
                    ->label(__('app.dimensions.name'))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('code')
                    ->label(__('app.dimensions.code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('app.dimensions.name'))
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('code')
            ->headerActions([
                CreateAction::make(),
            ])
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
