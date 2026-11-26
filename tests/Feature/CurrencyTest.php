<?php

namespace Tests\Feature;

use App\Models\Currency;
use Database\Seeders\CurrencySeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_an_active_currency_with_two_decimal_places(): void
    {
        $currency = Currency::factory()->create();

        $this->assertTrue($currency->is_active);
        $this->assertSame(2, $currency->decimal_places);
        $this->assertSame(3, strlen($currency->code));
    }

    public function test_code_must_be_unique(): void
    {
        Currency::factory()->create(['code' => 'EUR']);

        $this->expectException(QueryException::class);

        Currency::factory()->create(['code' => 'EUR']);
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(CurrencySeeder::class);
        $this->seed(CurrencySeeder::class);

        $this->assertSame(1, Currency::where('code', 'EUR')->count());
        $this->assertSame(0, Currency::where('code', 'JPY')->value('decimal_places'));
    }
}
