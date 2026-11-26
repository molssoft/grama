<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\CurrencyRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportEcbRatesTest extends TestCase
{
    use RefreshDatabase;

    private const XML = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<gesmes:Envelope xmlns:gesmes="http://www.gesmes.org/xml/2002-08-01" xmlns="http://www.ecb.int/vocabulary/2002-08-01/eurofxref">
  <gesmes:subject>Reference rates</gesmes:subject>
  <Cube>
    <Cube time="2026-11-25">
      <Cube currency="USD" rate="1.1500"/>
      <Cube currency="GBP" rate="0.8700"/>
      <Cube currency="XXX" rate="9.0000"/>
    </Cube>
    <Cube time="2026-11-24">
      <Cube currency="USD" rate="1.1400"/>
    </Cube>
  </Cube>
</gesmes:Envelope>
XML;

    private Currency $usd;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usd = Currency::factory()->create(['code' => 'USD']);
        Currency::factory()->create(['code' => 'GBP']);
    }

    public function test_it_imports_global_rates_for_known_currencies_only(): void
    {
        Http::fake(['*' => Http::response(self::XML)]);

        $this->artisan('currency-rates:import-ecb', ['--period' => '90d'])->assertSuccessful();

        $this->assertSame(3, CurrencyRate::count());
        $this->assertDatabaseHas('currency_rates', [
            'company_id' => null,
            'currency_id' => $this->usd->id,
            'date' => '2026-11-25',
            'rate' => '1.15000000',
            'source' => 'ecb',
        ]);
        Http::assertSent(fn ($request): bool => str_contains($request->url(), 'eurofxref-hist-90d.xml'));
    }

    public function test_running_twice_does_not_duplicate_rates(): void
    {
        Http::fake(['*' => Http::response(self::XML)]);

        $this->artisan('currency-rates:import-ecb')->assertSuccessful();
        $this->artisan('currency-rates:import-ecb')->assertSuccessful();

        $this->assertSame(3, CurrencyRate::count());
    }

    public function test_it_updates_ecb_rates_but_keeps_manual_ones(): void
    {
        $ecb = CurrencyRate::factory()->create(['currency_id' => $this->usd->id, 'date' => '2026-11-25', 'rate' => '1.00000000', 'source' => 'ecb']);
        $manual = CurrencyRate::factory()->create(['currency_id' => $this->usd->id, 'date' => '2026-11-24', 'rate' => '2.00000000', 'source' => 'manual']);
        Http::fake(['*' => Http::response(self::XML)]);

        $this->artisan('currency-rates:import-ecb')->assertSuccessful();

        $this->assertSame('1.15000000', $ecb->fresh()->rate);
        $this->assertSame('2.00000000', $manual->fresh()->rate);
    }

    public function test_it_fails_when_the_download_fails(): void
    {
        Http::fake(['*' => Http::response('', 500)]);

        $this->artisan('currency-rates:import-ecb')->assertFailed();

        $this->assertSame(0, CurrencyRate::count());
    }

    public function test_it_fails_on_invalid_xml(): void
    {
        Http::fake(['*' => Http::response('not xml')]);

        $this->artisan('currency-rates:import-ecb')->assertFailed();
    }

    public function test_it_rejects_an_unknown_period(): void
    {
        $this->artisan('currency-rates:import-ecb', ['--period' => 'week'])->assertFailed();
    }
}
