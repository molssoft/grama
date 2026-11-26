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
use App\Filament\Resources\CurrencyRates\Pages\ManageCurrencyRates;
use App\Models\Company;
use App\Models\Currency;
use App\Models\CurrencyRate;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CurrencyRateResourceTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private Currency $usd;

    protected function setUp(): void
    {
        parent::setUp();

        Currency::factory()->create(['code' => 'EUR']);
        $this->usd = Currency::factory()->create(['code' => 'USD']);
    }

    public function test_list_shows_global_rates_and_own_custom_rates_but_not_other_companies(): void
    {
        $this->signIn();
        $global = CurrencyRate::factory()->create(['currency_id' => $this->usd->id]);
        $own = CurrencyRate::factory()->forCompany($this->company)->create(['currency_id' => $this->usd->id]);
        $foreign = CurrencyRate::factory()->forCompany()->create(['currency_id' => $this->usd->id]);

        Livewire::test(ManageCurrencyRates::class)
            ->assertCanSeeTableRecords([$global, $own])
            ->assertCanNotSeeTableRecords([$foreign]);
    }

    public function test_it_filters_by_currency_and_date_range(): void
    {
        $this->signIn();
        $gbp = Currency::factory()->create(['code' => 'GBP']);
        $old = CurrencyRate::factory()->create(['currency_id' => $this->usd->id, 'date' => '2026-01-10']);
        $inRange = CurrencyRate::factory()->create(['currency_id' => $this->usd->id, 'date' => '2026-06-10']);
        $otherCurrency = CurrencyRate::factory()->create(['currency_id' => $gbp->id, 'date' => '2026-06-10']);

        Livewire::test(ManageCurrencyRates::class)
            ->filterTable('currency_id', [$this->usd->id])
            ->filterTable('date', ['from' => '2026-06-01', 'until' => '2026-06-30'])
            ->assertCanSeeTableRecords([$inRange])
            ->assertCanNotSeeTableRecords([$old, $otherCurrency]);
    }

    public function test_custom_rate_can_be_created_for_the_current_company(): void
    {
        $this->signIn();

        Livewire::test(ManageCurrencyRates::class)
            ->callAction('create', [
                'currency_id' => $this->usd->id,
                'date' => '2026-11-25',
                'rate' => '1.23456789',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('currency_rates', [
            'company_id' => $this->company->id,
            'currency_id' => $this->usd->id,
            'date' => '2026-11-25',
            'rate' => '1.23456789',
            'source' => 'manual',
        ]);
    }

    public function test_custom_rate_must_be_unique_per_currency_and_date(): void
    {
        $this->signIn();
        CurrencyRate::factory()->forCompany($this->company)->create(['currency_id' => $this->usd->id, 'date' => '2026-11-25']);

        Livewire::test(ManageCurrencyRates::class)
            ->callAction('create', [
                'currency_id' => $this->usd->id,
                'date' => '2026-11-25',
                'rate' => '1.2',
            ])
            ->assertHasFormErrors(['date']);
    }

    public function test_custom_rate_can_be_set_from_a_global_rate(): void
    {
        $this->signIn();
        $global = CurrencyRate::factory()->create(['currency_id' => $this->usd->id, 'date' => '2026-11-25', 'rate' => '1.10000000']);

        Livewire::test(ManageCurrencyRates::class)
            ->callAction(TestAction::make('setCustomRate')->table($global), ['rate' => '1.30'])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('currency_rates', ['company_id' => $this->company->id, 'currency_id' => $this->usd->id, 'date' => '2026-11-25', 'rate' => '1.30000000']);
        $this->assertSame('1.10000000', $global->fresh()->rate);
    }

    public function test_global_rates_cannot_be_edited_or_deleted(): void
    {
        $this->signIn();
        $global = CurrencyRate::factory()->create(['currency_id' => $this->usd->id]);

        Livewire::test(ManageCurrencyRates::class)
            ->assertTableActionHidden('edit', $global)
            ->assertTableActionHidden('delete', $global)
            ->assertTableActionVisible('setCustomRate', $global);
    }

    public function test_own_custom_rate_can_be_edited_and_deleted(): void
    {
        $this->signIn();
        $own = CurrencyRate::factory()->forCompany($this->company)->create(['currency_id' => $this->usd->id, 'date' => '2026-11-25']);

        Livewire::test(ManageCurrencyRates::class)
            ->callAction(TestAction::make('edit')->table($own), ['rate' => '2.5'])
            ->assertHasNoFormErrors()
            ->callAction(TestAction::make('delete')->table($own));

        $this->assertModelMissing($own);
    }

    public function test_user_without_the_permission_cannot_create_or_edit(): void
    {
        $this->signIn([]);
        $global = CurrencyRate::factory()->create(['currency_id' => $this->usd->id]);
        $own = CurrencyRate::factory()->forCompany($this->company)->create(['currency_id' => $this->usd->id]);

        Livewire::test(ManageCurrencyRates::class)
            ->assertActionHidden('create')
            ->assertTableActionHidden('setCustomRate', $global)
            ->assertTableActionHidden('edit', $own)
            ->assertTableActionHidden('delete', $own);
    }

    public function test_page_is_reachable_and_the_permission_can_be_granted(): void
    {
        $this->signIn([Permission::EditExchangeRates->value]);

        $this->get("/{$this->company->slug}/currency-rates")->assertOk();
        $this->assertTrue(auth()->user()->can('create', CurrencyRate::class));
    }

    /**
     * @param  list<string>|null  $permissions  null signs in as an administrator
     */
    private function signIn(?array $permissions = null): void
    {
        $user = User::factory()->create();
        $this->company = Company::factory()->create();
        $role = $permissions === null
            ? $this->company->administratorRole()
            : Role::factory()->create(['company_id' => $this->company->id, 'permissions' => $permissions]);
        $user->companies()->attach($this->company, ['role_id' => $role->id]);

        $this->actingAs($user);
        Filament::setCurrentPanel('admin');
        Filament::setTenant($this->company);
        $this->get("/{$this->company->slug}/currency-rates")->assertOk();
    }
}
