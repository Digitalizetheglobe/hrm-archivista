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
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'employee_type')) {
                $table->string('employee_type')->nullable()->after('company_doj');
            }
            if (!Schema::hasColumn('employees', 'confirm_of_employment')) {
                $table->boolean('confirm_of_employment')->default(false)->after('company_doj');
            }
            if (!Schema::hasColumn('employees', 'esic_no')) {
                $table->string('esic_no')->nullable()->after('company_doj');
            }
            if (!Schema::hasColumn('employees', 'bank_ac_no')) {
                $table->string('bank_ac_no')->nullable()->after('company_doj');
            }
            if (!Schema::hasColumn('employees', 'set_salary')) {
                $table->decimal('set_salary', 20, 2)->default(0.00)->after('company_doj');
            }
            if (!Schema::hasColumn('employees', 'tds_type')) {
                $table->string('tds_type')->default('old_regime')->after('company_doj');
            }
            if (!Schema::hasColumn('employees', 'salary_type')) {
                $table->integer('salary_type')->nullable()->after('company_doj');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'employee_type',
                'confirm_of_employment',
                'esic_no',
                'bank_ac_no',
                'set_salary',
                'tds_type',
            ]);
        });
    }
};
