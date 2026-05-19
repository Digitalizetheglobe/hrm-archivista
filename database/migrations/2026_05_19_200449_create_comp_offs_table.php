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
        Schema::table('comp_offs', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('id');
            $table->text('department_ids')->nullable()->after('branch_id');
            $table->text('dates')->nullable()->after('department_ids');
            $table->text('employee_ids')->nullable()->after('dates');
            $table->integer('created_by')->nullable()->after('employee_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comp_offs', function (Blueprint $table) {
            $table->dropColumn(['branch_id', 'department_ids', 'dates', 'employee_ids', 'created_by']);
        });
    }
};
