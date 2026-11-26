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
use App\Models\Currency;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Schema;

class EditCompanyProfile extends EditTenantProfile
{
    protected static ?string $slug = 'settings/company';

    public static function getLabel(): string
    {
        return __('app.companies.settings');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label(__('app.companies.name'))
                ->required()
                ->maxLength(255),
            TextInput::make('slug')
                ->label(__('app.companies.slug'))
                ->helperText(__('app.companies.slug_help'))
                ->required()
                ->maxLength(255)
                ->alphaDash()
                ->unique(Company::class, 'slug', ignoreRecord: true),
            Select::make('base_currency_id')
                ->label(__('app.companies.base_currency'))
                ->helperText(__('app.companies.base_currency_help'))
                ->options(fn (): array => Currency::query()
                    ->where('is_active', true)
                    ->orderBy('code')
                    ->get()
                    ->mapWithKeys(fn (Currency $currency): array => [$currency->id => $currency->code.' – '.$currency->name])
                    ->all())
                ->searchable()
                ->required(),
            Select::make('date_format')
                ->label(__('app.companies.date_format'))
                ->options(static::formatOptions(Company::DATE_FORMATS))
                ->required(),
            Select::make('datetime_format')
                ->label(__('app.companies.datetime_format'))
                ->options(static::formatOptions(Company::DATETIME_FORMATS))
                ->required(),
        ]);
    }

    /**
     * @param  list<string>  $formats
     * @return array<string, string>
     */
    private static function formatOptions(array $formats): array
    {
        $example = CarbonImmutable::create(2026, 12, 31, 14, 30);

        return collect($formats)
            ->mapWithKeys(fn (string $format): array => [$format => $example->format($format)])
            ->all();
    }
}
