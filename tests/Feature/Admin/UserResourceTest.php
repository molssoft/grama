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
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_admin_login(): void
    {
        $this->get('/users')->assertRedirect('/login');
    }

    public function test_user_list_shows_users(): void
    {
        $company = $this->signIn();
        $users = User::factory()->count(3)->create();
        $users->each(fn (User $user) => $user->companies()->attach($company));

        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords($users);
    }

    public function test_user_list_hides_users_of_other_companies(): void
    {
        $this->signIn();
        $outsider = User::factory()->create();
        $outsider->companies()->attach(Company::factory()->create());

        Livewire::test(ListUsers::class)->assertCanNotSeeTableRecords([$outsider]);
    }

    public function test_user_can_be_created_with_a_hashed_password(): void
    {
        $company = $this->signIn();

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Jane Accountant',
                'email' => 'jane@example.com',
                'password' => 'secret-pass-123',
                'locale' => 'lv',
                'role_id' => $company->administratorRole()->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'jane@example.com')->firstOrFail();

        $this->assertSame('lv', $user->locale);
        $this->assertSame($company->administratorRole()->id, $user->roleIn($company)->id);
        $this->assertNotSame('secret-pass-123', $user->password);
        $this->assertTrue(password_verify('secret-pass-123', $user->password));
    }

    public function test_existing_user_is_added_to_the_company_without_changing_their_account(): void
    {
        $company = $this->signIn();
        $existing = User::factory()->create(['name' => 'Original Name']);
        $originalPassword = $existing->password;
        $role = Role::factory()->create(['company_id' => $company->id]);

        Livewire::test(CreateUser::class)
            ->fillForm(['name' => 'Other Name', 'email' => $existing->email, 'role_id' => $role->id])
            ->call('create')
            ->assertHasNoFormErrors();

        $existing->refresh();
        $this->assertSame('Original Name', $existing->name);
        $this->assertSame($originalPassword, $existing->password);
        $this->assertSame($role->id, $existing->roleIn($company)->id);
        $this->assertSame(1, User::where('email', $existing->email)->count());
    }

    public function test_user_already_in_the_company_cannot_be_added_again(): void
    {
        $company = $this->signIn();
        $existing = User::factory()->create();
        $existing->companies()->attach($company);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Duplicate',
                'email' => $existing->email,
                'password' => 'secret-pass-123',
                'role_id' => $company->administratorRole()->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['email']);
    }

    public function test_user_role_can_be_changed(): void
    {
        $company = $this->signIn();
        $role = Role::factory()->create(['company_id' => $company->id]);
        $member = User::factory()->create();
        $member->companies()->attach($company, ['role_id' => $company->administratorRole()->id]);

        Livewire::test(EditUser::class, ['record' => $member->getRouteKey()])
            ->fillForm(['role_id' => $role->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($role->id, $member->roleIn($company)->id);
    }

    public function test_users_cannot_be_managed_without_the_edit_users_permission(): void
    {
        $company = $this->signIn(permissions: []);
        $member = User::factory()->create();
        $member->companies()->attach($company);

        $this->assertFalse(auth()->user()->can('create', User::class));
        $this->assertFalse(auth()->user()->can('update', $member));
        $this->assertFalse(auth()->user()->can('delete', $member));
    }

    public function test_users_can_be_managed_with_the_edit_users_permission(): void
    {
        $company = $this->signIn(permissions: [Permission::EditUsers->value]);
        $member = User::factory()->create();
        $member->companies()->attach($company);

        $this->assertTrue(auth()->user()->can('create', User::class));
        $this->assertTrue(auth()->user()->can('update', $member));
        $this->assertTrue(auth()->user()->can('delete', $member));
        $this->assertFalse(auth()->user()->can('delete', auth()->user()));
    }

    public function test_user_can_be_removed_from_the_company_but_keeps_their_account(): void
    {
        $company = $this->signIn();
        $member = User::factory()->create();
        $member->companies()->attach($company);

        Livewire::test(ListUsers::class)
            ->callTableAction('delete', $member);

        $this->assertFalse($member->companies()->whereKey($company->id)->exists());
        $this->assertModelExists($member);
    }

    public function test_administrator_cannot_remove_themselves(): void
    {
        $this->signIn();

        Livewire::test(ListUsers::class)
            ->assertTableActionHidden('delete', auth()->user());

        Livewire::test(EditUser::class, ['record' => auth()->user()->getRouteKey()])
            ->assertActionHidden('delete');
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

        return $company;
    }
}
