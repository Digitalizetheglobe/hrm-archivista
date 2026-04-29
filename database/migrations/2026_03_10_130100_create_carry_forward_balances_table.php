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
        Schema::create('carry_forward_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained()->onDelete('cascade');
            $table->string('month'); // Format: '2024-01'
            $table->decimal('carried_forward_days', 5, 2)->default(0.00);
            $table->decimal('allocated_days', 5, 2)->default(0.00);
            $table->decimal('used_days', 5, 2)->default(0.00);
            $table->decimal('remaining_days', 5, 2)->default(0.00);
            $table->timestamps();
            
            // Unique constraint to prevent duplicate entries
            $table->unique(['employee_id', 'leave_type_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carry_forward_balances');
    }
};
