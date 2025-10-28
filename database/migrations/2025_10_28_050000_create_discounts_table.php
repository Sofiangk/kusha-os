<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('discounts', function (Blueprint $table) {
      $table->id();
      $table->string('name_ar', 100);
      $table->string('code', 50)->unique()->nullable();
      $table->enum('type', ['percentage', 'fixed_amount']);
      $table->decimal('value', 10, 2);
      $table->decimal('minimum_order_amount', 10, 2)->nullable();
      $table->dateTime('valid_from')->nullable();
      $table->dateTime('valid_to')->nullable();
      $table->boolean('is_active')->default(true);
      $table->text('description_ar')->nullable();
      $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
      $table->softDeletes();
      $table->timestamps();

      $table->index(['code', 'is_active']);
      $table->index(['valid_from', 'valid_to']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('discounts');
  }
};
