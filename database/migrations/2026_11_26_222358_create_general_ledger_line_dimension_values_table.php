<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tags a ledger line with one value per dimension. The dimension is kept next to the value so that
     * the database itself guarantees a line never has two values of the same dimension.
     */
    public function up(): void
    {
        Schema::create('general_ledger_line_dimension_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('general_ledger_line_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dimension_id')->constrained()->restrictOnDelete();
            $table->foreignId('dimension_value_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['general_ledger_line_id', 'dimension_id'], 'gl_line_dimension_unique');
            $table->index('dimension_value_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_ledger_line_dimension_values');
    }
};
