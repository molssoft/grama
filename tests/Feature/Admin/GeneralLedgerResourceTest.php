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

use App\Filament\Resources\GeneralLedgers\Pages\CreateGeneralLedger;
use App\Filament\Resources\GeneralLedgers\Pages\EditGeneralLedger;
use App\Filament\Resources\GeneralLedgers\Pages\ListGeneralLedgers;
use App\Models\Account;
use App\Models\Company;
use App\Models\GeneralLedger;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GeneralLedgerResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_shows_only_the_current_companys_entries(): void
    {
        $other = GeneralLedger::factory()->create();
        $company = $this->signIn();
        $own = GeneralLedger::factory()->create(['company_id' => $company->id]);

        Livewire::test(ListGeneralLedgers::class)
            ->assertCanSeeTableRecords([$own])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_entry_is_created_with_a_debit_and_credit_line_per_row(): void
    {
        $company = $this->signIn();
        $cash = Account::factory()->create(['company_id' => $company->id]);
        $revenue = Account::factory()->create(['company_id' => $company->id]);

        Livewire::test(CreateGeneralLedger::class)
            ->fillForm([
                'number' => 'JE-1',
                'date' => '2026-11-26',
                'rows' => [
                    ['debit_account_id' => $cash->id, 'credit_account_id' => $revenue->id, 'amount' => 50],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $entry = GeneralLedger::where('number', 'JE-1')->firstOrFail();
        $this->assertSame($company->id, $entry->company_id);
        $this->assertSame(auth()->id(), $entry->created_by);
        $this->assertCount(2, $entry->lines);
        $this->assertSame([1], $entry->lines->pluck('group_id')->unique()->all());
        $this->assertTrue($entry->isBalanced());
    }

    public function test_debit_and_credit_account_must_differ(): void
    {
        $company = $this->signIn();
        $cash = Account::factory()->create(['company_id' => $company->id]);

        Livewire::test(CreateGeneralLedger::class)
            ->fillForm([
                'number' => 'JE-1',
                'date' => '2026-11-26',
                'rows' => [
                    ['debit_account_id' => $cash->id, 'credit_account_id' => $cash->id, 'amount' => 50],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['rows.0.credit_account_id']);

        $this->assertDatabaseCount('general_ledgers', 0);
    }

    public function test_editing_replaces_the_rows(): void
    {
        $company = $this->signIn();
        $cash = Account::factory()->create(['company_id' => $company->id]);
        $revenue = Account::factory()->create(['company_id' => $company->id]);
        $entry = GeneralLedger::factory()->create(['company_id' => $company->id]);
        $entry->replacePairRows([['debit_account_id' => $cash->id, 'credit_account_id' => $revenue->id, 'amount' => '10.00']]);

        Livewire::test(EditGeneralLedger::class, ['record' => $entry->getKey()])
            ->assertFormSet(function (array $state) use ($cash, $revenue): void {
                $this->assertEquals([$cash->id, $revenue->id], [array_values($state['rows'])[0]['debit_account_id'], array_values($state['rows'])[0]['credit_account_id']]);
            })
            ->fillForm(['rows' => [
                ['debit_account_id' => $revenue->id, 'credit_account_id' => $cash->id, 'amount' => 25],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        $entry->refresh();
        $this->assertCount(2, $entry->lines);
        $this->assertSame('25.00', $entry->lines->first()->debit);
        $this->assertSame($revenue->id, $entry->lines->first()->account_id);
    }

    public function test_user_without_permission_cannot_create_entries(): void
    {
        $this->signIn([]);

        $this->get('/'.Filament::getTenant()->slug.'/general-ledgers/create')->assertForbidden();
    }

    private function signIn(?array $permissions = null): Company
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $role = $permissions === null
            ? $company->administratorRole()
            : Role::factory()->create(['company_id' => $company->id, 'permissions' => $permissions]);
        $user->companies()->attach($company, ['role_id' => $role->id]);

        $this->actingAs($user);
        Filament::setCurrentPanel('admin');
        Filament::setTenant($company);

        $this->get("/{$company->slug}/general-ledgers")->assertOk();

        return $company;
    }
}
