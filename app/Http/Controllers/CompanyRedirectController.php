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

namespace App\Http\Controllers;

use App\Filament\Tenancy\SelectCompany;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Http\Controllers\RedirectToTenantController;
use Illuminate\Http\RedirectResponse;

class CompanyRedirectController extends RedirectToTenantController
{
    /**
     * Replaces Filament's tenant redirect on the panel root.
     * Sends the user to their only company, to the company chooser when
     * they have several, or to company registration when they have none.
     */
    public function __invoke(): RedirectResponse
    {
        $panel = Filament::getCurrentOrDefaultPanel();

        /** @var User $user */
        $user = Filament::auth()->user();
        $companies = $user->getTenants($panel);

        return match ($companies->count()) {
            0 => redirect($panel->getTenantRegistrationUrl()),
            1 => redirect(Filament::getUrl($companies->first())),
            default => redirect(SelectCompany::getUrl()),
        };
    }
}
