<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Company;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_main_page_shows_settings_users_menu(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company);

        $this->actingAs($user)
            ->get(Filament::getUrl($company))
            ->assertOk()
            ->assertSee(__('app.navigation.settings'))
            ->assertSee(__('app.users.plural'))
            ->assertSee('/'.$company->slug.'/users');
    }
}
