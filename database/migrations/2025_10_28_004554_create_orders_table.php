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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique(); // ORD000001, ORD000002... (supports up to 999,999 orders)
            $table->foreignId('client_id')->constrained('clients')->onDelete('restrict');
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('status', [
                'placed',
                'preparing',
                'ready_to_ship',
                'shipped',
                'delivered',
                'refunded',
                'canceled'
            ])->default('placed');
            $table->dateTime('delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();

            $table->index('order_number');
            $table->index('status');
            $table->index('client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
