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
     * Rates are quoted against EUR (1 EUR = rate units of the currency). A row without a company is a
     * global rate; a row with a company is that company's override.
     */
    public function up(): void
    {
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('rate', 18, 8);
            $table->string('source')->default('manual');
            $table->timestamps();

            $table->unique(['company_id', 'currency_id', 'date']);
            $table->index(['currency_id', 'date']);
        });

        // A unique index treats NULLs as distinct, so global rates need their own index.
        DB::statement('create unique index currency_rates_global_unique on currency_rates (currency_id, date) where company_id is null');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_rates');
    }
};
