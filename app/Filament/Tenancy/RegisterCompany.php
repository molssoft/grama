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

namespace App\Filament\Tenancy;

use App\Models\Company;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class RegisterCompany extends RegisterTenant
{
    public static function getLabel(): string
    {
        return __('app.companies.create');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label(__('app.companies.name'))
                ->required()
                ->maxLength(255)
                ->autofocus()
                ->live(debounce: 300)
                ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ?string $old): void {
                    $slug = $get('slug');
                    $followsName = blank($slug) || $slug === Company::generateUniqueSlug($old ?? '');

                    if ($followsName) {
                        $set('slug', filled($state) ? Company::generateUniqueSlug($state) : null);
                    }
                }),
            TextInput::make('slug')
                ->label(__('app.companies.slug'))
                ->helperText(__('app.companies.slug_help'))
                ->required()
                ->maxLength(255)
                ->alphaDash()
                ->unique(Company::class, 'slug')
                ->live(onBlur: true),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(array $data): Model
    {
        $company = parent::handleRegistration($data);

        $company->users()->attach(auth()->user(), ['role_id' => $company->administratorRole()?->getKey()]);

        return $company;
    }
}
