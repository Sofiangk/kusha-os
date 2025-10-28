<?php

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\User;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $adminUser = User::where('email', 'admin@kushaos.test')->first();

    // 1. Family Discount - 15% off minimum 100 LYD
    Discount::create([
      'name_ar' => 'خصم عائلي',
      'code' => 'FAMILY15',
      'type' => 'percentage',
      'value' => 15.00,
      'minimum_order_amount' => 100.00,
      'valid_from' => null,
      'valid_to' => null,
      'is_active' => true,
      'description_ar' => 'خصم 15% على الطلبات العائلية الكبيرة',
      'created_by' => $adminUser->id,
    ]);

    // 2. Ramadan Special - 20% off, expires in 3 months
    Discount::create([
      'name_ar' => 'عرض رمضان',
      'code' => 'RAMADAN20',
      'type' => 'percentage',
      'value' => 20.00,
      'minimum_order_amount' => 50.00,
      'valid_from' => now(),
      'valid_to' => now()->addMonths(3),
      'is_active' => true,
      'description_ar' => 'عرض خاص لشهر رمضان المبارك - خصم 20%',
      'created_by' => $adminUser->id,
    ]);

    // 3. Loyalty VIP - 10% off for repeat customers
    Discount::create([
      'name_ar' => 'خصم العملاء المميزين',
      'code' => 'VIP10',
      'type' => 'percentage',
      'value' => 10.00,
      'minimum_order_amount' => 80.00,
      'valid_from' => null,
      'valid_to' => null,
      'is_active' => true,
      'description_ar' => 'خصم خاص للعملاء المميزين والمتكررين',
      'created_by' => $adminUser->id,
    ]);

    // 4. End of Season - 25 LYD fixed discount, expires in 30 days
    Discount::create([
      'name_ar' => 'خصم نهاية الموسم',
      'code' => 'SEASON25',
      'type' => 'fixed_amount',
      'value' => 25.00,
      'minimum_order_amount' => 120.00,
      'valid_from' => now(),
      'valid_to' => now()->addDays(30),
      'is_active' => true,
      'description_ar' => 'خصم ثابت 25 دينار على الطلبات الكبيرة',
      'created_by' => $adminUser->id,
    ]);

    // 5. New Customer - 5% off, no minimum
    Discount::create([
      'name_ar' => 'خصم العملاء الجدد',
      'code' => 'WELCOME5',
      'type' => 'percentage',
      'value' => 5.00,
      'minimum_order_amount' => null,
      'valid_from' => null,
      'valid_to' => null,
      'is_active' => true,
      'description_ar' => 'ترحيب بجميع العملاء الجدد',
      'created_by' => $adminUser->id,
    ]);
  }
}
