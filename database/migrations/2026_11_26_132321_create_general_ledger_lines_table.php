<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Debit and credit are always in the company's base currency. When a line was entered in another
     * currency, that currency, the amount and the rate used are kept alongside.
     *
     * Lines of one entry that belong together (a debit and its matching credit, as entered on one row)
     * share a group id, and every group balances on its own.
     */
    public function up(): void
    {
        Schema::create('general_ledger_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('general_ledger_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('group_id');
            $table->string('description')->nullable();
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->foreignId('currency_id')->nullable()->constrained()->restrictOnDelete();
            $table->decimal('currency_amount', 18, 2)->nullable();
            $table->decimal('exchange_rate', 18, 8)->nullable();
            $table->timestamps();

            $table->index(['general_ledger_id', 'group_id']);
            $table->index(['account_id', 'general_ledger_id']);
        });

        // SQLite (used by the tests) cannot add a constraint to an existing table; the model enforces the same rule.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('alter table general_ledger_lines add constraint general_ledger_lines_amounts_check check (debit >= 0 and credit >= 0 and (debit = 0 or credit = 0))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_ledger_lines');
    }
};
