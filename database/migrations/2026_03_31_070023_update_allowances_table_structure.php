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
        Schema::table('allowances', function (Blueprint $table) {
            // Drop old columns that are no longer needed
            $table->dropColumn(['allowance_option', 'title', 'type']);
            
            // Add new columns to match deduction structure
            $table->string('allowance_type')->after('employee_id');
            $table->string('month')->after('allowance_type');
            $table->text('remark')->nullable()->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allowances', function (Blueprint $table) {
            // Add back old columns
            $table->integer('allowance_option')->after('employee_id');
            $table->string('title')->after('allowance_option');
            $table->string('type')->nullable()->after('amount');
            
            // Drop new columns
            $table->dropColumn(['allowance_type', 'month', 'remark']);
        });
    }
};
