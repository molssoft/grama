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

use App\Enums\AccountType;
use App\Enums\Permission;
use App\Filament\Resources\Accounts\Pages\CreateAccount;
use App\Filament\Resources\Accounts\Pages\EditAccount;
use App\Filament\Resources\Accounts\Pages\ListAccounts;
use App\Models\Account;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Livewire\Livewire;
use Tests\TestCase;

class AccountResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_shows_only_the_current_companys_accounts(): void
    {
        $other = Account::factory()->create(['company_id' => Company::factory()->create()->id]);
        $company = $this->signIn();
        $own = Account::factory()->create(['company_id' => $company->id]);

        Livewire::test(ListAccounts::class)
            ->searchTable($own->code)
            ->assertCanSeeTableRecords([$own])
            ->assertCanNotSeeTableRecords([$other]);

        Livewire::test(ListAccounts::class)
            ->searchTable($other->code)
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_account_is_created_for_the_current_company(): void
    {
        $company = $this->signIn();

        Livewire::test(CreateAccount::class)
            ->fillForm(['code' => '9110', 'name' => 'Cash', 'type' => AccountType::Asset->value])
            ->call('create')
            ->assertHasNoFormErrors();

        $account = Account::where('code', '9110')->firstOrFail();
        $this->assertSame($company->id, $account->company_id);
        $this->assertSame(AccountType::Asset, $account->type);
        $this->assertTrue($account->is_active);
    }

    public function test_code_must_be_unique_within_the_company(): void
    {
        $company = $this->signIn();
        Account::factory()->create(['company_id' => $company->id, 'code' => '9110']);

        Livewire::test(CreateAccount::class)
            ->fillForm(['code' => '9110', 'name' => 'Cash', 'type' => AccountType::Asset->value])
            ->call('create')
            ->assertHasFormErrors(['code' => 'unique']);
    }

    public function test_same_code_is_allowed_in_another_company(): void
    {
        Account::factory()->create(['code' => '9110']);
        $this->signIn();

        Livewire::test(CreateAccount::class)
            ->fillForm(['code' => '9110', 'name' => 'Cash', 'type' => AccountType::Asset->value])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_parent_options_exclude_other_companies_the_account_itself_and_its_descendants(): void
    {
        $other = Account::factory()->create();
        $company = $this->signIn();
        $account = Account::factory()->heading()->create(['company_id' => $company->id]);
        $child = Account::factory()->heading()->create(['company_id' => $company->id, 'parent_id' => $account->id, 'type' => $account->type]);
        $grandchild = Account::factory()->create(['company_id' => $company->id, 'parent_id' => $child->id, 'type' => $account->type]);
        $unrelated = Account::factory()->heading()->create(['company_id' => $company->id, 'type' => $account->type]);
        $postable = Account::factory()->create(['company_id' => $company->id]);

        $this->assertEqualsCanonicalizing([$child->id, $grandchild->id], $account->descendantIds());

        Livewire::test(EditAccount::class, ['record' => $account->getRouteKey()])
            ->assertFormFieldExists('parent_id', function ($field) use ($unrelated, $postable, $other, $account, $child, $grandchild): bool {
                // Searching by the shared "9" prefix of the factory codes avoids the preload limit of the default chart.
                $options = $field->getSearchResults('9');

                return array_key_exists($unrelated->id, $options)
                    && ! array_key_exists($postable->id, $options)
                    && ! array_key_exists($other->id, $options)
                    && ! array_key_exists($account->id, $options)
                    && ! array_key_exists($child->id, $options)
                    && ! array_key_exists($grandchild->id, $options);
            });
    }

    public function test_account_with_children_cannot_be_deleted(): void
    {
        $company = $this->signIn();
        $parent = Account::factory()->heading()->create(['company_id' => $company->id]);

        $this->assertTrue(auth()->user()->can('delete', $parent));

        Account::factory()->create(['company_id' => $company->id, 'parent_id' => $parent->id, 'type' => $parent->type]);

        $this->assertFalse(auth()->user()->can('delete', $parent));
    }

    public function test_accounts_cannot_be_managed_without_the_permission(): void
    {
        $company = $this->signIn(permissions: []);
        $account = Account::factory()->create(['company_id' => $company->id]);

        $this->assertFalse(auth()->user()->can('create', Account::class));
        $this->assertFalse(auth()->user()->can('update', $account));
        $this->assertFalse(auth()->user()->can('delete', $account));
    }

    public function test_accounts_can_be_managed_with_the_permission(): void
    {
        $company = $this->signIn(permissions: [Permission::EditChartOfAccounts->value]);
        $account = Account::factory()->create(['company_id' => $company->id]);

        $this->assertTrue(auth()->user()->can('create', Account::class));
        $this->assertTrue(auth()->user()->can('update', $account));
        $this->assertTrue(auth()->user()->can('delete', $account));
    }

    public function test_new_company_gets_the_default_latvian_chart_with_hierarchy(): void
    {
        $company = Company::factory()->create();

        $this->assertSame(count(Account::DEFAULT_CHART), $company->accounts()->count());

        $cash = $company->accounts()->where('code', '2610')->firstOrFail();
        $this->assertSame('Kase', $cash->name);
        $this->assertSame(AccountType::Asset, $cash->type);
        $this->assertSame('26', $cash->parent->code);
        $this->assertSame('2', $cash->parent->parent->code);
        $this->assertNull($cash->parent->parent->parent_id);

        $this->assertSame(AccountType::Revenue, $company->accounts()->where('code', '6110')->firstOrFail()->type);
        $this->assertSame(AccountType::Expense, $company->accounts()->where('code', '7210')->firstOrFail()->type);
    }

    public function test_default_chart_has_unique_codes_and_matching_parent_types(): void
    {
        $codes = array_column(Account::DEFAULT_CHART, 0);
        $this->assertSame($codes, array_unique($codes));

        $company = Company::factory()->create();
        $company->accounts()->with('parent')->get()->each(function (Account $account): void {
            if ($account->parent !== null) {
                $this->assertSame($account->parent->type, $account->type);
            }
        });
    }

    public function test_default_chart_does_not_leak_into_other_companies(): void
    {
        $first = Company::factory()->create();
        $second = Company::factory()->create();

        $this->assertSame(
            [],
            $second->accounts()->whereIn('parent_id', $first->accounts()->pluck('id'))->pluck('id')->all(),
        );
    }

    public function test_account_must_have_the_same_type_as_its_parent(): void
    {
        $company = Company::factory()->create();
        $parent = $company->accounts()->where('code', '26')->firstOrFail();

        $this->expectException(InvalidArgumentException::class);

        Account::factory()->create(['company_id' => $company->id, 'parent_id' => $parent->id, 'type' => AccountType::Expense]);
    }

    public function test_default_chart_marks_only_accounts_without_children_as_postable(): void
    {
        $company = Company::factory()->create();

        foreach (['1', '26', '57'] as $heading) {
            $this->assertFalse($company->accounts()->where('code', $heading)->firstOrFail()->is_postable);
        }

        foreach (['2610', '5710', '7210'] as $leaf) {
            $this->assertTrue($company->accounts()->where('code', $leaf)->firstOrFail()->is_postable);
        }

        $company->accounts()->with('children')->get()->each(function (Account $account): void {
            $this->assertSame($account->children->isEmpty(), $account->is_postable);
        });
    }

    public function test_postable_account_cannot_be_a_parent(): void
    {
        $company = Company::factory()->create();
        $cash = $company->accounts()->where('code', '2610')->firstOrFail();

        $this->expectException(InvalidArgumentException::class);

        Account::factory()->create(['company_id' => $company->id, 'parent_id' => $cash->id, 'type' => $cash->type]);
    }

    public function test_account_with_children_cannot_be_made_postable(): void
    {
        $company = Company::factory()->create();
        $group = $company->accounts()->where('code', '26')->firstOrFail();

        $this->expectException(InvalidArgumentException::class);

        $group->update(['is_postable' => true]);
    }

    public function test_account_gets_children_after_it_is_turned_into_a_heading(): void
    {
        $company = Company::factory()->create();
        $cash = $company->accounts()->where('code', '2610')->firstOrFail();

        $cash->update(['is_postable' => false]);
        $analytical = Account::factory()->create(['company_id' => $company->id, 'parent_id' => $cash->id, 'type' => $cash->type]);

        $this->assertTrue($analytical->is_postable);
        $this->assertFalse($cash->fresh()->is_postable);
    }

    public function test_child_code_suggestion_continues_the_numbering_of_existing_children(): void
    {
        $company = Company::factory()->create();
        $suggest = fn (string $code): string => $company->accounts()->where('code', $code)->firstOrFail()->suggestChildCode();

        $this->assertSame('2630', $suggest('26'));
        $this->assertSame('5750', $suggest('57'));
        $this->assertSame('78', $suggest('7'));
    }

    public function test_child_code_suggestion_for_a_parent_without_children(): void
    {
        $company = Company::factory()->create();
        $class = Account::factory()->heading()->create(['company_id' => $company->id, 'code' => '90']);
        $group = Account::factory()->heading()->create(['company_id' => $company->id, 'code' => '9']);
        $synthetic = Account::factory()->heading()->create(['company_id' => $company->id, 'code' => '9100']);

        $this->assertSame('9010', $class->suggestChildCode());
        $this->assertSame('91', $group->suggestChildCode());
        $this->assertSame('9100-001', $synthetic->suggestChildCode());

        Account::factory()->create(['company_id' => $company->id, 'parent_id' => $synthetic->id, 'type' => $synthetic->type, 'code' => '9100-001']);

        $this->assertSame('9100-002', $synthetic->suggestChildCode());
    }

    public function test_child_code_suggestion_skips_codes_already_taken(): void
    {
        $company = Company::factory()->create();
        $group = $company->accounts()->where('code', '26')->firstOrFail();
        Account::factory()->create(['company_id' => $company->id, 'code' => '2630']);

        $this->assertSame('2640', $group->suggestChildCode());
    }

    public function test_choosing_a_parent_suggests_a_code_without_overwriting_a_typed_one(): void
    {
        $company = $this->signIn();
        $group = $company->accounts()->where('code', '26')->firstOrFail();
        $other = $company->accounts()->where('code', '57')->firstOrFail();

        Livewire::test(CreateAccount::class)
            ->fillForm(['parent_id' => $group->id])
            ->assertFormSet(['code' => '2630'])
            ->fillForm(['parent_id' => $other->id])
            ->assertFormSet(['code' => '5750']);

        Livewire::test(CreateAccount::class)
            ->fillForm(['code' => '9999'])
            ->fillForm(['parent_id' => $group->id])
            ->assertFormSet(['code' => '9999']);
    }

    public function test_type_follows_the_chosen_parent_in_the_form(): void
    {
        $company = $this->signIn();
        $parent = $company->accounts()->where('code', '7')->firstOrFail();

        Livewire::test(CreateAccount::class)
            ->fillForm(['code' => '9110', 'name' => 'Test', 'parent_id' => $parent->id])
            ->assertFormSet(['type' => AccountType::Expense])
            ->assertFormFieldDisabled('type')
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(AccountType::Expense, Account::where('code', '9110')->firstOrFail()->type);
    }

    /**
     * @param  list<string>|null  $permissions  null signs in as the company administrator
     */
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

        // A real request runs the tenancy middleware, which registers the tenant scope and ownership hooks.
        $this->get("/{$company->slug}/accounts")->assertOk();

        return $company;
    }
}
