<?php

use App\Models\Role;
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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });

        Schema::table('company_user', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('user_id')->constrained('roles')->nullOnDelete();
        });

        $this->backfillExistingCompanies();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_user', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('roles');
    }

    /**
     * Give every existing company the default administrator role and assign it to its current members.
     */
    private function backfillExistingCompanies(): void
    {
        foreach (DB::table('companies')->pluck('id') as $companyId) {
            $roleId = DB::table('roles')->insertGetId([
                'company_id' => $companyId,
                'name' => Role::ADMINISTRATOR_NAME,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('company_user')->where('company_id', $companyId)->update(['role_id' => $roleId]);
        }
    }
};
