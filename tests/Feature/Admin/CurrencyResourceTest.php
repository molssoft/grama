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

use App\Filament\Resources\Currencies\CurrencyResource;
use App\Filament\Resources\Currencies\Pages\ListCurrencies;
use App\Models\Company;
use App\Models\Currency;
use App\Models\CurrencyRate;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CurrencyResourceTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->company = Company::factory()->create();
        $user->companies()->attach($this->company, ['role_id' => $this->company->administratorRole()->id]);

        $this->actingAs($user);
        Filament::setCurrentPanel('admin');
        Filament::setTenant($this->company);
    }

    public function test_page_is_reachable_and_lists_all_currencies(): void
    {
        $this->get("/{$this->company->slug}/currencies")->assertOk();

        $currencies = Currency::factory()->count(3)->create();

        Livewire::test(ListCurrencies::class)->assertCanSeeTableRecords($currencies);
    }

    public function test_it_shows_the_latest_global_rate_but_not_company_overrides(): void
    {
        $currency = Currency::factory()->create(['code' => 'USD']);
        CurrencyRate::factory()->create(['currency_id' => $currency->id, 'date' => '2026-11-24', 'rate' => '1.10000000']);
        CurrencyRate::factory()->create(['currency_id' => $currency->id, 'date' => '2026-11-25', 'rate' => '1.15000000']);
        CurrencyRate::factory()->forCompany($this->company)->create(['currency_id' => $currency->id, 'date' => '2026-11-26', 'rate' => '9.00000000']);

        Livewire::test(ListCurrencies::class)
            ->assertTableColumnStateSet('latest_rate', '1.15', $currency)
            ->assertSee('25.11.2026');
    }

    public function test_currencies_are_read_only(): void
    {
        $this->assertFalse(CurrencyResource::canCreate());
        $this->assertSame(['index'], array_keys(CurrencyResource::getPages()));
    }
}
