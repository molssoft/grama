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

namespace Tests\Feature\Admin;

use App\Enums\Permission;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Tenancy\EditCompanyProfile;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompanySettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_is_served_at_settings_company(): void
    {
        $company = $this->signIn();

        $this->get("/{$company->slug}/settings/company")->assertOk()->assertSee('Company settings')->assertSee('Base currency');
    }

    public function test_guests_are_redirected_to_the_login(): void
    {
        $company = Company::factory()->create();

        $this->get("/{$company->slug}/settings/company")->assertRedirect('/login');
    }

    public function test_base_currency_can_be_selected_and_saved(): void
    {
        $company = $this->signIn();
        $currency = Currency::factory()->create(['code' => 'EUR']);

        Livewire::test(EditCompanyProfile::class)
            ->fillForm(['base_currency_id' => $currency->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($company->fresh()->baseCurrency->is($currency));
    }

    public function test_date_formats_default_to_latvian_style_and_can_be_changed(): void
    {
        $company = $this->signIn();
        $currency = Currency::factory()->create();

        $this->assertSame('d.m.Y', $company->fresh()->date_format);
        $this->assertSame('d.m.Y H:i', $company->fresh()->datetime_format);

        Livewire::test(EditCompanyProfile::class)
            ->fillForm(['base_currency_id' => $currency->id, 'date_format' => 'Y-m-d', 'datetime_format' => 'Y-m-d H:i'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Y-m-d', $company->fresh()->date_format);
        $this->assertSame('Y-m-d H:i', $company->fresh()->datetime_format);
    }

    public function test_only_listed_date_formats_are_accepted(): void
    {
        $this->signIn();

        Livewire::test(EditCompanyProfile::class)
            ->fillForm(['date_format' => 'D, d M y'])
            ->call('save')
            ->assertHasFormErrors(['date_format']);
    }

    public function test_tables_use_the_company_date_format(): void
    {
        $company = $this->signIn();
        $company->update(['date_format' => 'Y-m-d', 'datetime_format' => 'Y-m-d H:i']);
        Filament::setTenant($company->fresh());

        $table = UserResource::table(Table::make(Livewire::new(ListUsers::class)));

        $this->assertSame('Y-m-d', $table->getDefaultDateDisplayFormat());
        $this->assertSame('Y-m-d H:i', $table->getDefaultDateTimeDisplayFormat());
    }

    public function test_base_currency_is_required(): void
    {
        $this->signIn();

        Livewire::test(EditCompanyProfile::class)
            ->fillForm(['base_currency_id' => null])
            ->call('save')
            ->assertHasFormErrors(['base_currency_id' => 'required']);
    }

    public function test_inactive_currencies_are_not_selectable(): void
    {
        $this->signIn();
        $inactive = Currency::factory()->inactive()->create();

        Livewire::test(EditCompanyProfile::class)
            ->fillForm(['base_currency_id' => $inactive->id])
            ->call('save')
            ->assertHasFormErrors(['base_currency_id']);
    }

    public function test_member_without_the_permission_cannot_open_the_page(): void
    {
        $company = $this->signIn(withPermission: false);

        $this->get("/{$company->slug}/settings/company")->assertNotFound();
    }

    public function test_role_with_the_permission_can_open_the_page(): void
    {
        $company = $this->signIn(withPermission: false);
        $role = Role::factory()->create([
            'company_id' => $company->id,
            'permissions' => [Permission::EditCompanyData->value],
        ]);
        auth()->user()->companies()->updateExistingPivot($company->id, ['role_id' => $role->id]);

        $this->get("/{$company->slug}/settings/company")->assertOk();
    }

    private function signIn(bool $withPermission = true): Company
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company, [
            'role_id' => $withPermission ? $company->administratorRole()->id : null,
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel('admin');
        Filament::setTenant($company);

        return $company;
    }
}
