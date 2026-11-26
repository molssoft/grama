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

namespace App\Filament\Resources\Dimensions\Schemas;

use App\Models\Dimension;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class DimensionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label(__('app.dimensions.code'))
                    ->required()
                    ->maxLength(32)
                    ->unique(
                        Dimension::class,
                        'code',
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule): Unique => $rule->where('company_id', Filament::getTenant()?->getKey()),
                    ),
                TextInput::make('name')
                    ->label(__('app.dimensions.name'))
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
