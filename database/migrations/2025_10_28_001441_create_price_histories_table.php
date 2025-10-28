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
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('old_price', 10, 2);
            $table->decimal('new_price', 10, 2);
            $table->foreignId('changed_by')->constrained('users')->onDelete('restrict');
            $table->enum('reason', [
                'manual_update',
                'seasonal_adjustment',
                'cost_increase',
                'promotion_ended',
                'supplier_change',
                'other'
            ])->default('manual_update');
            $table->timestamps();

            $table->index('product_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};
