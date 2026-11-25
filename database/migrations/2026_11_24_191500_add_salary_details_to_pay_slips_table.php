<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pay_slips', function (Blueprint $table) {
            if (!Schema::hasColumn('pay_slips', 'salary_details')) {
                $table->longText('salary_details')->nullable()->after('overtime');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pay_slips', function (Blueprint $table) {
            if (Schema::hasColumn('pay_slips', 'salary_details')) {
                $table->dropColumn('salary_details');
            }
        });
    }
};

