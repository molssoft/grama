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

use App\Filament\Resources\Dimensions\Pages\CreateDimension;
use App\Filament\Resources\Dimensions\Pages\EditDimension;
use App\Filament\Resources\Dimensions\Pages\ListDimensions;
use App\Filament\Resources\Dimensions\RelationManagers\ValuesRelationManager;
use App\Filament\Resources\GeneralLedgers\Pages\CreateGeneralLedger;
use App\Filament\Resources\GeneralLedgers\Pages\EditGeneralLedger;
use App\Models\Account;
use App\Models\Company;
use App\Models\Dimension;
use App\Models\DimensionValue;
use App\Models\GeneralLedger;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DimensionResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_shows_only_the_current_companys_dimensions(): void
    {
        $other = Dimension::factory()->create();
        $company = $this->signIn();
        $own = Dimension::factory()->create(['company_id' => $company->id]);

        Livewire::test(ListDimensions::class)
            ->assertCanSeeTableRecords([$own])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_dimension_is_created_for_the_current_company(): void
    {
        $company = $this->signIn();

        Livewire::test(CreateDimension::class)
            ->fillForm(['code' => 'DEPT', 'name' => 'Department'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('dimensions', ['company_id' => $company->id, 'code' => 'DEPT']);
    }

    public function test_dimension_code_is_unique_within_a_company_only(): void
    {
        Dimension::factory()->create(['code' => 'DEPT']);
        $company = $this->signIn();
        Dimension::factory()->create(['company_id' => $company->id, 'code' => 'PROJ']);

        Livewire::test(CreateDimension::class)
            ->fillForm(['code' => 'PROJ', 'name' => 'Duplicate'])
            ->call('create')
            ->assertHasFormErrors(['code']);

        Livewire::test(CreateDimension::class)
            ->fillForm(['code' => 'DEPT', 'name' => 'Department'])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_values_are_managed_on_the_dimension(): void
    {
        $company = $this->signIn();
        $dimension = Dimension::factory()->create(['company_id' => $company->id]);

        Livewire::test(ValuesRelationManager::class, ['ownerRecord' => $dimension, 'pageClass' => EditDimension::class])
            ->callAction(TestAction::make('create')->table(), ['code' => 'SALES', 'name' => 'Sales'])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('dimension_values', ['dimension_id' => $dimension->id, 'code' => 'SALES']);
    }

    public function test_user_without_permission_cannot_create_dimensions(): void
    {
        $this->signIn([]);

        $this->get('/'.Filament::getTenant()->slug.'/dimensions/create')->assertForbidden();
    }

    public function test_entry_rows_are_saved_with_dimension_values_on_both_lines(): void
    {
        $company = $this->signIn();
        $cash = Account::factory()->create(['company_id' => $company->id]);
        $revenue = Account::factory()->create(['company_id' => $company->id]);
        $department = Dimension::factory()->create(['company_id' => $company->id]);
        $sales = DimensionValue::factory()->create(['dimension_id' => $department->id]);

        Livewire::test(CreateGeneralLedger::class)
            ->fillForm([
                'number' => 'JE-1',
                'date' => '2026-11-26',
                'rows' => [
                    ['debit_account_id' => $cash->id, 'credit_account_id' => $revenue->id, 'amount' => 50, 'dimensions' => [$department->id => $sales->id]],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $entry = GeneralLedger::where('number', 'JE-1')->firstOrFail();
        $this->assertCount(2, $entry->lines);
        foreach ($entry->lines as $line) {
            $this->assertSame([$sales->id], $line->dimensionValues->pluck('id')->all());
            $this->assertSame($department->id, $line->dimensionValues->first()->pivot->dimension_id);
        }
    }

    public function test_editing_an_entry_shows_and_keeps_its_dimension_values(): void
    {
        $company = $this->signIn();
        $cash = Account::factory()->create(['company_id' => $company->id]);
        $revenue = Account::factory()->create(['company_id' => $company->id]);
        $department = Dimension::factory()->create(['company_id' => $company->id]);
        $sales = DimensionValue::factory()->create(['dimension_id' => $department->id]);
        $entry = GeneralLedger::factory()->create(['company_id' => $company->id]);
        $entry->replacePairRows([['debit_account_id' => $cash->id, 'credit_account_id' => $revenue->id, 'amount' => '10.00', 'dimensions' => [$department->id => $sales->id]]]);

        Livewire::test(EditGeneralLedger::class, ['record' => $entry->getKey()])
            ->assertFormSet(function (array $state) use ($department, $sales): void {
                $this->assertEquals($sales->id, array_values($state['rows'])[0]['dimensions'][$department->id]);
            })
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(2, $entry->fresh()->lines()->has('dimensionValues')->count());
    }

    public function test_value_of_another_company_is_rejected(): void
    {
        $foreign = DimensionValue::factory()->create();
        $company = $this->signIn();
        $cash = Account::factory()->create(['company_id' => $company->id]);
        $revenue = Account::factory()->create(['company_id' => $company->id]);
        $entry = GeneralLedger::factory()->create(['company_id' => $company->id]);

        $this->expectException(\InvalidArgumentException::class);

        $entry->replacePairRows([['debit_account_id' => $cash->id, 'credit_account_id' => $revenue->id, 'amount' => '10.00', 'dimensions' => [$foreign->dimension_id => $foreign->id]]]);
    }

    public function test_a_value_used_in_the_ledger_cannot_be_deleted(): void
    {
        $company = $this->signIn();
        $cash = Account::factory()->create(['company_id' => $company->id]);
        $revenue = Account::factory()->create(['company_id' => $company->id]);
        $department = Dimension::factory()->create(['company_id' => $company->id]);
        $used = DimensionValue::factory()->create(['dimension_id' => $department->id]);
        $unused = DimensionValue::factory()->create(['dimension_id' => $department->id]);
        $entry = GeneralLedger::factory()->create(['company_id' => $company->id]);
        $entry->replacePairRows([['debit_account_id' => $cash->id, 'credit_account_id' => $revenue->id, 'amount' => '10.00', 'dimensions' => [$department->id => $used->id]]]);

        $user = auth()->user();
        $this->assertFalse($user->can('delete', $used));
        $this->assertFalse($user->can('delete', $department));
        $this->assertTrue($user->can('delete', $unused));
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

        $this->get("/{$company->slug}/dimensions")->assertOk();

        return $company;
    }
}
