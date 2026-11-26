<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('is_postable')->default(true)->after('type');
        });

        // Accounts that group other accounts are headings: postings go only to accounts without children.
        DB::table('accounts')
            ->whereIn('id', DB::table('accounts')->whereNotNull('parent_id')->select('parent_id'))
            ->update(['is_postable' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('is_postable');
        });
    }
};
