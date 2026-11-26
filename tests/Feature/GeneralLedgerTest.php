<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Company;
use App\Models\GeneralLedger;
use App\Models\GeneralLedgerLine;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class GeneralLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_entry_is_balanced_when_debits_equal_credits(): void
    {
        $entry = GeneralLedger::factory()->create();
        GeneralLedgerLine::factory()->for($entry)->create(['debit' => '100.50']);
        GeneralLedgerLine::factory()->for($entry)->credit('100.50')->create();

        $this->assertTrue($entry->isBalanced());
    }

    public function test_entry_is_unbalanced_when_totals_differ(): void
    {
        $entry = GeneralLedger::factory()->create();
        GeneralLedgerLine::factory()->for($entry)->create(['debit' => '100.00']);
        GeneralLedgerLine::factory()->for($entry)->credit('99.00')->create();

        $this->assertFalse($entry->isBalanced());
    }

    public function test_each_group_must_balance_on_its_own(): void
    {
        $entry = GeneralLedger::factory()->create();
        GeneralLedgerLine::factory()->for($entry)->create(['group_id' => 1, 'debit' => '10.00']);
        GeneralLedgerLine::factory()->for($entry)->credit('5.00')->create(['group_id' => 1]);
        GeneralLedgerLine::factory()->for($entry)->create(['group_id' => 2, 'debit' => '5.00']);
        GeneralLedgerLine::factory()->for($entry)->credit('10.00')->create(['group_id' => 2]);

        $this->assertFalse($entry->isBalanced());
    }

    public function test_pair_rows_round_trip_through_grouped_lines(): void
    {
        $entry = GeneralLedger::factory()->create();
        $debit = Account::factory()->create(['company_id' => $entry->company_id]);
        $credit = Account::factory()->create(['company_id' => $entry->company_id]);

        $entry->replacePairRows([
            ['debit_account_id' => $debit->id, 'credit_account_id' => $credit->id, 'description' => 'A', 'amount' => '12.50', 'dimensions' => []],
            ['debit_account_id' => $credit->id, 'credit_account_id' => $debit->id, 'description' => 'B', 'amount' => '3.00', 'dimensions' => []],
        ]);

        $entry->refresh();
        $this->assertCount(4, $entry->lines);
        $this->assertSame([1, 1, 2, 2], $entry->lines->pluck('group_id')->all());
        $this->assertTrue($entry->isBalanced());
        $this->assertSame([
            ['debit_account_id' => $debit->id, 'credit_account_id' => $credit->id, 'description' => 'A', 'amount' => '12.50', 'dimensions' => []],
            ['debit_account_id' => $credit->id, 'credit_account_id' => $debit->id, 'description' => 'B', 'amount' => '3.00', 'dimensions' => []],
        ], $entry->pairRows());
    }

    public function test_number_is_unique_per_company(): void
    {
        $company = Company::factory()->create();
        GeneralLedger::factory()->for($company)->create(['number' => 'JE-1']);

        $this->expectException(QueryException::class);

        GeneralLedger::factory()->for($company)->create(['number' => 'JE-1']);
    }

    public function test_line_cannot_have_both_debit_and_credit(): void
    {
        $this->expectException(InvalidArgumentException::class);

        GeneralLedgerLine::factory()->create(['debit' => '10.00', 'credit' => '10.00']);
    }

    public function test_deleting_entry_deletes_its_lines(): void
    {
        $entry = GeneralLedger::factory()->create();
        GeneralLedgerLine::factory()->for($entry)->create();

        $entry->delete();

        $this->assertDatabaseCount('general_ledger_lines', 0);
    }
}
