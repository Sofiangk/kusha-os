<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('orders', function (Blueprint $table) {
      $table->decimal('subtotal', 10, 2)->default(0)->after('client_id');
      $table->foreignId('discount_id')->nullable()->after('subtotal')->constrained('discounts')->nullOnDelete();
      $table->string('discount_name')->nullable()->after('discount_id');
      $table->string('discount_code')->nullable()->after('discount_name');
      $table->enum('discount_type', ['percentage', 'fixed_amount'])->nullable()->after('discount_code');
      $table->decimal('discount_value', 10, 2)->nullable()->after('discount_type');
      $table->decimal('discount_amount', 10, 2)->default(0)->after('discount_value');
    });
  }

  public function down(): void
  {
    Schema::table('orders', function (Blueprint $table) {
      $table->dropForeign(['discount_id']);
      $table->dropColumn([
        'subtotal',
        'discount_id',
        'discount_name',
        'discount_code',
        'discount_type',
        'discount_value',
        'discount_amount'
      ]);
    });
  }
};
