<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->date('start_date')->after('employee_id')->nullable();
            $table->date('end_date')->after('start_date')->nullable();
        });

        // Copy data if needed, but the user said "Remove the old date column"
        // and "currently there is only a single date column"
        // Let's copy existing date to start_date and end_date first
        DB::table('site_visits')->update([
            'start_date' => DB::raw('date'),
            'end_date' => DB::raw('date'),
        ]);

        Schema::table('site_visits', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->date('date')->after('employee_id')->nullable();
        });

        DB::table('site_visits')->update([
            'date' => DB::raw('start_date'),
        ]);

        Schema::table('site_visits', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};
