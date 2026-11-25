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
        Schema::table('payroll_data', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_data', 'custom_allowances')) {
                $table->json('custom_allowances')->nullable()->after('executive');
            }
            if (!Schema::hasColumn('payroll_data', 'custom_deductions')) {
                $table->json('custom_deductions')->nullable()->after('tds');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_data', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_data', 'custom_allowances')) {
                $table->dropColumn('custom_allowances');
            }
            if (Schema::hasColumn('payroll_data', 'custom_deductions')) {
                $table->dropColumn('custom_deductions');
            }
        });
    }
};

