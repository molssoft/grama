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
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Concerns\HasRoutes;
use Filament\Pages\SimplePage;
use Filament\Panel;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;

class SelectCompany extends SimplePage
{
    use HasRoutes;

    protected string $view = 'filament.pages.select-company';

    public static function getSlug(?Panel $panel = null): string
    {
        return 'companies';
    }

    public static function getRelativeRouteName(Panel $panel): string
    {
        return 'select-company';
    }

    public static function getUrl(): string
    {
        return route('filament.'.Filament::getCurrentOrDefaultPanel()->getId().'.select-company');
    }

    public function getTitle(): string|Htmlable
    {
        return __('app.companies.select');
    }

    public function hasLogo(): bool
    {
        return false;
    }

    /**
     * @return Collection<int, Company>
     */
    public function getCompanies(): Collection
    {
        /** @var User $user */
        $user = Filament::auth()->user();

        return $user->getTenants(Filament::getCurrentOrDefaultPanel());
    }

    public function getCompanyUrl(Company $company): string
    {
        return Filament::getUrl($company);
    }
}
