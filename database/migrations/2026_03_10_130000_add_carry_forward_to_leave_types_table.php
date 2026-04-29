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
        Schema::table('leave_types', function (Blueprint $table) {
            $table->boolean('carry_forward_enabled')->default(false)->after('is_unlimited');
            $table->decimal('max_carry_forward_days', 5, 2)->default(0.00)->after('carry_forward_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn(['carry_forward_enabled', 'max_carry_forward_days']);
        });
    }
};
