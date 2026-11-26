<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A value of a dimension, e.g. "Sales" for Department.
     */
    public function up(): void
    {
        Schema::create('dimension_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dimension_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->timestamps();

            $table->unique(['dimension_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dimension_values');
    }
};
