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

use App\Filament\Tenancy\RegisterCompany;
use App\Filament\Tenancy\SelectCompany;
use App\Models\Company;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompanySelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_companies_is_sent_to_company_registration(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertRedirect(Filament::getTenantRegistrationUrl());
    }

    public function test_user_with_one_company_goes_straight_to_it(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company);

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(Filament::getUrl($company));
    }

    public function test_user_with_several_companies_must_choose_one(): void
    {
        $user = User::factory()->create();
        $user->companies()->attach(Company::factory()->count(2)->create());

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(SelectCompany::getUrl());
    }

    public function test_chooser_lists_only_the_users_companies(): void
    {
        $user = User::factory()->create();
        $user->companies()->attach(Company::factory()->create(['name' => 'Alpha SIA']));
        Company::factory()->create(['name' => 'Foreign SIA']);

        $this->actingAs($user)
            ->get(SelectCompany::getUrl())
            ->assertOk()
            ->assertSee('Alpha SIA')
            ->assertDontSee('Foreign SIA');
    }

    public function test_registering_a_company_links_it_to_the_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Filament::setCurrentPanel('admin');

        Livewire::test(RegisterCompany::class)
            ->fillForm(['name' => 'New SIA', 'slug' => 'new-sia'])
            ->call('register')
            ->assertHasNoFormErrors();

        $company = Company::where('name', 'New SIA')->firstOrFail();
        $this->assertTrue($user->companies()->whereKey($company->id)->exists());
    }

    public function test_slug_is_suggested_from_the_company_name(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel('admin');
        Company::factory()->create(['slug' => 'berzu-nams']);

        Livewire::test(RegisterCompany::class)
            ->fillForm(['name' => 'Bērzu nams'])
            ->assertFormSet(['slug' => 'berzu-nams-2']);
    }

    public function test_slug_follows_the_name_until_edited_by_hand(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel('admin');

        Livewire::test(RegisterCompany::class)
            ->set('data.name', 'Ab')
            ->assertFormSet(['slug' => 'ab'])
            ->set('data.name', 'Abc')
            ->assertFormSet(['slug' => 'abc'])
            ->set('data.slug', 'custom')
            ->set('data.name', 'Abcd')
            ->assertFormSet(['slug' => 'custom']);
    }

    public function test_slug_must_be_unique_and_url_safe(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel('admin');
        Company::factory()->create(['slug' => 'taken']);

        Livewire::test(RegisterCompany::class)
            ->fillForm(['name' => 'X', 'slug' => 'taken'])
            ->call('register')
            ->assertHasFormErrors(['slug' => 'unique']);

        Livewire::test(RegisterCompany::class)
            ->fillForm(['name' => 'X', 'slug' => 'bad slug!'])
            ->call('register')
            ->assertHasFormErrors(['slug']);
    }

    public function test_company_url_uses_the_slug(): void
    {
        $company = Company::factory()->create(['slug' => 'my-company']);

        $this->assertStringEndsWith('/my-company', Filament::getUrl($company));
    }

    public function test_company_name_is_required(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel('admin');

        Livewire::test(RegisterCompany::class)
            ->fillForm(['name' => ''])
            ->call('register')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_user_cannot_open_a_company_they_do_not_belong_to(): void
    {
        $user = User::factory()->create();
        $foreign = Company::factory()->create();

        $this->actingAs($user)
            ->get(Filament::getUrl($foreign))
            ->assertNotFound();
    }
}
