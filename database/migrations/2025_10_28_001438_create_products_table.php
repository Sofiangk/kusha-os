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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 20)->unique();
            $table->string('name_ar', 150);
            $table->text('description_ar')->nullable();
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->decimal('base_price', 10, 2);
            $table->unsignedInteger('minimum_order_quantity')->default(1);
            $table->boolean('allow_below_minimum')->default(false);
            $table->boolean('is_available')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['category_id', 'is_available']);
            $table->index('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
