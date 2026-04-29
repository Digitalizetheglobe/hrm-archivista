<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocationFieldsToAttendanceEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->string('clock_in_latitude')->nullable()->after('total_rest');
            $table->string('clock_in_longitude')->nullable()->after('clock_in_latitude');
            $table->text('clock_in_location')->nullable()->after('clock_in_longitude');
            $table->string('clock_out_latitude')->nullable()->after('clock_in_location');
            $table->string('clock_out_longitude')->nullable()->after('clock_out_latitude');
            $table->text('clock_out_location')->nullable()->after('clock_out_longitude');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->dropColumn([
                'clock_in_latitude',
                'clock_in_longitude', 
                'clock_in_location',
                'clock_out_latitude',
                'clock_out_longitude',
                'clock_out_location'
            ]);
        });
    }
}
