<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Currency;
use App\Models\CurrencyRate;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyRateTest extends TestCase
{
    use RefreshDatabase;

    private Currency $eur;

    private Currency $usd;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eur = Currency::factory()->create(['code' => 'EUR']);
        $this->usd = Currency::factory()->create(['code' => 'USD']);
    }

    public function test_only_one_global_rate_per_currency_and_date(): void
    {
        CurrencyRate::factory()->create(['currency_id' => $this->usd->id, 'date' => '2026-11-25']);

        $this->expectException(QueryException::class);

        CurrencyRate::factory()->create(['currency_id' => $this->usd->id, 'date' => '2026-11-25']);
    }

    public function test_only_one_override_per_company_currency_and_date(): void
    {
        $company = Company::factory()->create();
        CurrencyRate::factory()->forCompany($company)->create(['currency_id' => $this->usd->id, 'date' => '2026-11-25']);

        $this->expectException(QueryException::class);

        CurrencyRate::factory()->forCompany($company)->create(['currency_id' => $this->usd->id, 'date' => '2026-11-25']);
    }

    public function test_global_rate_and_overrides_of_different_companies_can_share_a_date(): void
    {
        CurrencyRate::factory()->create(['currency_id' => $this->usd->id, 'date' => '2026-11-25']);
        CurrencyRate::factory()->forCompany()->create(['currency_id' => $this->usd->id, 'date' => '2026-11-25']);
        CurrencyRate::factory()->forCompany()->create(['currency_id' => $this->usd->id, 'date' => '2026-11-25']);

        $this->assertSame(3, CurrencyRate::count());
    }

    public function test_rate_falls_back_to_the_latest_earlier_date(): void
    {
        CurrencyRate::factory()->create(['currency_id' => $this->usd->id, 'date' => '2026-11-20', 'rate' => '1.10000000']);
        CurrencyRate::factory()->create(['currency_id' => $this->usd->id, 'date' => '2026-11-24', 'rate' => '1.20000000']);

        $this->assertSame('1.20000000', CurrencyRate::rateAgainstPivot($this->usd, now()->setDate(2026, 11, 29)));
        $this->assertSame('1.10000000', CurrencyRate::rateAgainstPivot($this->usd, now()->setDate(2026, 11, 23)));
        $this->assertNull(CurrencyRate::rateAgainstPivot($this->usd, now()->setDate(2026, 11, 1)));
    }

    public function test_company_override_wins_on_the_same_date_but_only_for_that_company(): void
    {
        $company = Company::factory()->create();
        CurrencyRate::factory()->create(['currency_id' => $this->usd->id, 'date' => '2026-11-25', 'rate' => '1.10000000']);
        CurrencyRate::factory()->forCompany($company)->create(['currency_id' => $this->usd->id, 'date' => '2026-11-25', 'rate' => '1.50000000']);
        $date = now()->setDate(2026, 11, 25);

        $this->assertSame('1.50000000', CurrencyRate::rateAgainstPivot($this->usd, $date, $company));
        $this->assertSame('1.10000000', CurrencyRate::rateAgainstPivot($this->usd, $date, Company::factory()->create()));
        $this->assertSame('1.10000000', CurrencyRate::rateAgainstPivot($this->usd, $date));
    }

    public function test_older_override_does_not_hide_a_newer_global_rate(): void
    {
        $company = Company::factory()->create();
        CurrencyRate::factory()->forCompany($company)->create(['currency_id' => $this->usd->id, 'date' => '2026-11-10', 'rate' => '1.50000000']);
        CurrencyRate::factory()->create(['currency_id' => $this->usd->id, 'date' => '2026-11-20', 'rate' => '1.10000000']);

        $this->assertSame('1.10000000', CurrencyRate::rateAgainstPivot($this->usd, now()->setDate(2026, 11, 25), $company));
    }

    public function test_rate_between_converts_through_the_eur_pivot(): void
    {
        $gbp = Currency::factory()->create(['code' => 'GBP']);
        $date = now()->setDate(2026, 11, 25);
        CurrencyRate::factory()->create(['currency_id' => $this->usd->id, 'date' => '2026-11-25', 'rate' => '1.25000000']);
        CurrencyRate::factory()->create(['currency_id' => $gbp->id, 'date' => '2026-11-25', 'rate' => '0.80000000']);

        $this->assertSame('1.25000000', CurrencyRate::rateBetween($this->eur, $this->usd, $date));
        $this->assertSame('0.80000000', CurrencyRate::rateBetween($this->usd, $this->eur, $date));
        $this->assertSame('0.64000000', CurrencyRate::rateBetween($this->usd, $gbp, $date));
        $this->assertSame('1.00000000', CurrencyRate::rateBetween($gbp, $gbp, $date));
    }

    public function test_rate_between_is_null_when_a_rate_is_missing(): void
    {
        $this->assertNull(CurrencyRate::rateBetween($this->eur, $this->usd, now()));
    }
}
