<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateVariablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('letter_templates')->onDelete('cascade');
            $table->string('variable_name'); // e.g., {employee_name}
            $table->string('field_label'); // e.g., Employee Name
            $table->string('field_type')->default('text'); // text, date, select, number, email, textarea
            $table->json('validation_rules')->nullable(); // JSON validation rules
            $table->json('field_options')->nullable(); // For select fields
            $table->string('default_value')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->unique(['template_id', 'variable_name']);
            $table->index(['template_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('template_variables');
    }
}
