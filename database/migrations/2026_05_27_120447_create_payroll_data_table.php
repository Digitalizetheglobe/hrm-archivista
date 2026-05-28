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
        Schema::create('payroll_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            
            // Allowances (percentages)
            $table->decimal('basic', 5, 2)->default(0);
            $table->decimal('medical', 5, 2)->default(0);
            $table->decimal('hra', 5, 2)->default(0);
            $table->decimal('conveyance', 5, 2)->default(0);
            $table->decimal('education', 5, 2)->default(0);
            $table->decimal('executive', 5, 2)->default(0);
            
            // Deductions (amounts)
            $table->decimal('esi', 15, 2)->default(0);
            $table->decimal('pf', 15, 2)->default(0);
            $table->decimal('professional_tax', 15, 2)->default(0);

            $table->timestamps();

            // Foreign key
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_data');
    }
};
