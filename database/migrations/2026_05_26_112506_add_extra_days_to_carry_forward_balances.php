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
            $table->decimal('extra_days', 5, 2)->default(0.00)->after('carried_forward_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carry_forward_balances', function (Blueprint $table) {
            $table->dropColumn('extra_days');
        });
    }
};
