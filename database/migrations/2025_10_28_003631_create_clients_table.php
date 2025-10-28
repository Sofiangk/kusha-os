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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar', 150);
            $table->string('phone', 20)->unique();
            $table->string('address_ar')->nullable();
            $table->text('notes')->nullable();
            $table->integer('total_orders')->default(0); // For quick analytics
            $table->decimal('total_spent', 12, 2)->default(0); // For quick analytics
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();

            $table->index('phone');
            $table->index('total_orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
