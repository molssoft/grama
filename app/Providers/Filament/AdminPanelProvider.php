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

namespace App\Providers\Filament;

use App\Filament\Resources\CurrencyRates\Pages\ManageCurrencyRates;
use App\Filament\Tenancy\EditCompanyProfile;
use App\Filament\Tenancy\RegisterCompany;
use App\Filament\Tenancy\SelectCompany;
use App\Http\Middleware\SetLocale;
use App\Models\Company;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\View\TablesRenderHook;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->login()
            ->tenant(Company::class, slugAttribute: 'slug')
            ->tenantRegistration(RegisterCompany::class)
            ->tenantProfile(EditCompanyProfile::class)
            ->navigationGroups([
                NavigationGroup::make(fn (): string => __('app.navigation.accounting')),
                NavigationGroup::make(fn (): string => __('app.navigation.currencies')),
                NavigationGroup::make(fn (): string => __('app.navigation.settings')),
            ])
            ->navigationItems([
                NavigationItem::make(fn (): string => __('app.companies.settings'))
                    ->group(fn (): string => __('app.navigation.settings'))
                    ->icon(Heroicon::OutlinedBuildingOffice)
                    ->sort(20)
                    ->visible(fn (): bool => Filament::getTenant() !== null && auth()->user()?->can('update', Filament::getTenant()))
                    ->url(fn (): ?string => Filament::getTenantProfileUrl())
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.tenant.profile')),
            ])
            ->authenticatedRoutes(function (Panel $panel): void {
                SelectCompany::registerRoutes($panel);
            })
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): View => view('components.language-switcher'),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<style>.fi-sidebar-nav-groups{row-gap:.5rem}</style>',
            )
            ->renderHook(
                TablesRenderHook::TOOLBAR_SEARCH_BEFORE,
                fn (): View => view('filament.currency-rates.today-button'),
                scopes: ManageCurrencyRates::class,
            )
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetLocale::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
