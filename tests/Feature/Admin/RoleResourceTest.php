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
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoleResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_shows_only_the_current_companys_roles(): void
    {
        $other = Company::factory()->create();
        $company = $this->signIn();

        Livewire::test(ListRoles::class)
            ->assertCanSeeTableRecords(Role::withoutGlobalScopes()->whereBelongsTo($company)->get())
            ->assertCanNotSeeTableRecords(Role::withoutGlobalScopes()->whereBelongsTo($other)->get());
    }

    public function test_role_is_created_for_the_current_company_with_permissions(): void
    {
        $company = $this->signIn();

        Livewire::test(CreateRole::class)
            ->fillForm(['name' => 'Grāmatvedis', 'permissions' => [Permission::EditCompanyData->value]])
            ->call('create')
            ->assertHasNoFormErrors();

        $role = Role::where('name', 'Grāmatvedis')->firstOrFail();
        $this->assertSame($company->id, $role->company_id);
        $this->assertTrue($role->hasPermission(Permission::EditCompanyData));
    }

    public function test_role_name_must_be_unique_within_the_company(): void
    {
        $this->signIn();

        Livewire::test(CreateRole::class)
            ->fillForm(['name' => Role::ADMINISTRATOR_NAME])
            ->call('create')
            ->assertHasFormErrors(['name' => 'unique']);
    }

    public function test_same_name_is_allowed_in_another_company(): void
    {
        Role::factory()->create(['name' => 'Grāmatvedis']);
        $this->signIn();

        Livewire::test(CreateRole::class)
            ->fillForm(['name' => 'Grāmatvedis'])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_administrator_role_always_has_every_permission(): void
    {
        $company = $this->signIn();

        foreach (Permission::cases() as $permission) {
            $this->assertTrue($company->administratorRole()->hasPermission($permission));
        }
    }

    public function test_administrator_role_cannot_be_deleted(): void
    {
        $company = $this->signIn();

        $this->assertFalse(auth()->user()->can('delete', $company->administratorRole()));
    }

    public function test_role_assigned_to_members_cannot_be_deleted(): void
    {
        $company = $this->signIn();
        $role = Role::factory()->create(['company_id' => $company->id]);

        $this->assertTrue(auth()->user()->can('delete', $role));

        User::factory()->create()->companies()->attach($company, ['role_id' => $role->id]);

        $this->assertFalse(auth()->user()->can('delete', $role));
    }

    public function test_administrator_name_is_not_editable(): void
    {
        $company = $this->signIn();

        Livewire::test(EditRole::class, ['record' => $company->administratorRole()->getRouteKey()])
            ->assertFormFieldDisabled('name')
            ->assertFormFieldDisabled('permissions');
    }

    public function test_roles_cannot_be_managed_without_the_edit_roles_permission(): void
    {
        $company = $this->signIn(permissions: []);
        $role = Role::factory()->create(['company_id' => $company->id]);

        $this->assertFalse(auth()->user()->can('create', Role::class));
        $this->assertFalse(auth()->user()->can('update', $role));
        $this->assertFalse(auth()->user()->can('delete', $role));
    }

    public function test_roles_can_be_managed_with_the_edit_roles_permission(): void
    {
        $company = $this->signIn(permissions: [Permission::EditRoles->value]);
        $role = Role::factory()->create(['company_id' => $company->id]);

        $this->assertTrue(auth()->user()->can('create', Role::class));
        $this->assertTrue(auth()->user()->can('update', $role));
        $this->assertTrue(auth()->user()->can('delete', $role));
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
        $this->get("/{$company->slug}/roles")->assertOk();

        return $company;
    }
}
