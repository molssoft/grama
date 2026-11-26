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

namespace Tests\Feature;

use App\Filament\Tenancy\RegisterCompany;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_company_gets_the_administrators_role(): void
    {
        $company = Company::factory()->create();

        $this->assertSame(['Administratori'], $company->roles()->pluck('name')->all());
    }

    public function test_registering_a_company_makes_the_creator_an_administrator(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Filament::setCurrentPanel('admin');

        Livewire::test(RegisterCompany::class)
            ->fillForm(['name' => 'New SIA', 'slug' => 'new-sia'])
            ->call('register')
            ->assertHasNoFormErrors();

        $company = Company::where('slug', 'new-sia')->firstOrFail();

        $this->assertSame(Role::ADMINISTRATOR_NAME, $user->roleIn($company)?->name);
    }

    public function test_role_is_stored_per_user_per_company(): void
    {
        $user = User::factory()->create();
        $first = Company::factory()->create();
        $second = Company::factory()->create();
        $custom = Role::factory()->create(['company_id' => $second->id, 'name' => 'Grāmatvedis']);

        $user->companies()->attach($first, ['role_id' => $first->administratorRole()->id]);
        $user->companies()->attach($second, ['role_id' => $custom->id]);

        $this->assertSame('Administratori', $user->roleIn($first)->name);
        $this->assertSame('Grāmatvedis', $user->roleIn($second)->name);
    }

    public function test_role_names_are_unique_within_a_company(): void
    {
        $company = Company::factory()->create();

        $this->expectException(QueryException::class);

        $company->roles()->create(['name' => Role::ADMINISTRATOR_NAME]);
    }

    public function test_user_without_a_role_has_none(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company);

        $this->assertNull($user->roleIn($company));
    }
}
