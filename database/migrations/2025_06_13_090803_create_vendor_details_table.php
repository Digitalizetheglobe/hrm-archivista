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
        Schema::create('vendor_details', function (Blueprint $table) {
    $table->id();
    
    // Contact Details
    $table->date('contact_date');
    $table->string('name');
    $table->text('address');
    $table->string('contact_person');
    $table->string('contact_person_phone');
    $table->string('email');
    $table->string('company_website')->nullable();
    $table->string('experience')->nullable();
    $table->string('plan_location')->nullable();
    
    // Product Details
    $table->foreignId('category_id')->constrained();
    $table->foreignId('sub_category_id')->constrained('sub_categories');
    $table->string('product');
    $table->string('product_image')->nullable();
    $table->text('area_of_application')->nullable();
    $table->text('bag_description')->nullable();
    $table->decimal('rate_in_pure', 10, 2);
    $table->decimal('for_supply_rate', 10, 2);
    $table->decimal('for_apply_rate', 10, 2);
    
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_details');
    }
};
