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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('requested_plan')->default(0)->after('plan_expire_date');
            $table->date('trial_expire_date')->nullable()->after('requested_plan');
            $table->integer('trial_plan')->default(0)->after('trial_expire_date');
            $table->integer('is_login_enable')->default(1)->after('trial_plan');
            $table->integer('referral_code')->default(0)->after('is_active');
            $table->integer('used_referral_code')->default(0)->after('referral_code');
            $table->integer('commission_amount')->default(0)->after('used_referral_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'requested_plan',
                'trial_expire_date', 
                'trial_plan',
                'is_login_enable',
                'referral_code',
                'used_referral_code',
                'commission_amount'
            ]);
        });
    }
};
