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
        Schema::table('carry_forward_balances', function (Blueprint $table) {
            // Add a period_type column to distinguish between monthly and yearly
            $table->string('period_type')->default('monthly')->after('month'); // 'monthly' or 'yearly'
            
            // Add indexes for better performance with shorter names
            $table->index(['employee_id', 'leave_type_id', 'period_type', 'month'], 'cf_balance_idx');
            
            // Drop the old unique constraint and add a new one that includes period_type
            $table->dropUnique(['employee_id', 'leave_type_id', 'month']);
            $table->unique(['employee_id', 'leave_type_id', 'month', 'period_type'], 'cf_balance_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carry_forward_balances', function (Blueprint $table) {
            // Drop the new unique constraint and index
            $table->dropUnique('cf_balance_unique');
            $table->dropIndex('cf_balance_idx');
            
            // Drop the period_type column
            $table->dropColumn('period_type');
            
            // Recreate the old unique constraint
            $table->unique(['employee_id', 'leave_type_id', 'month']);
        });
    }
};
