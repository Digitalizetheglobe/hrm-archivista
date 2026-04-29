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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->integer('clients_group_id')->nullable();
            $table->string('client_code', 50)->nullable();
            $table->string('client_name')->nullable();
            $table->text('client_address')->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('gst_no', 50)->nullable();
            $table->string('company_phone', 20)->nullable();
            $table->string('company_email', 100)->nullable();
            $table->string('contact_person_name', 100)->nullable();
            $table->string('contact_person_phone', 20)->nullable();
            $table->text('remark')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
};
