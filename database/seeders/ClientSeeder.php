<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('email', 'admin@kushaos.test')->first();

        $clients = [
            [
                'name_ar' => 'محمد أحمد',
                'phone' => '+218912345678',
                'address_ar' => 'شارع الجمهورية، بنغازي',
                'notes' => 'عميل مفضل - يطلب بشكل منتظم',
                'total_orders' => 15,
                'total_spent' => 450.00,
            ],
            [
                'name_ar' => 'فاطمة علي',
                'phone' => '+218923456789',
                'address_ar' => 'حي الزهراء، بنغازي',
                'notes' => 'تفضل بيتي فور بالشوكولاتة',
                'total_orders' => 8,
                'total_spent' => 240.00,
            ],
            [
                'name_ar' => 'علي حسن',
                'phone' => '+218934567890',
                'address_ar' => 'شارع عمر المختار، مصراتة',
                'notes' => null,
                'total_orders' => 3,
                'total_spent' => 90.00,
            ],
            [
                'name_ar' => 'آمنة سالم',
                'phone' => '+218945678901',
                'address_ar' => 'حي الرحبة، بنغازي',
                'notes' => 'طلبات موسمية - رمضان',
                'total_orders' => 12,
                'total_spent' => 360.00,
            ],
            [
                'name_ar' => 'خالد إبراهيم',
                'phone' => '+218956789012',
                'address_ar' => 'طريق الهواري، بنغازي',
                'notes' => null,
                'total_orders' => 5,
                'total_spent' => 150.00,
            ],
            [
                'name_ar' => 'سارة محمد',
                'phone' => '+218967890123',
                'address_ar' => 'حي بن يونس، بنغازي',
                'notes' => 'تطلب حلويات للعيادة',
                'total_orders' => 20,
                'total_spent' => 600.00,
            ],
            [
                'name_ar' => 'حمزة عبدالله',
                'phone' => '+218978901234',
                'address_ar' => 'شارع أول سبتمبر، بنغازي',
                'notes' => 'عميل جديد',
                'total_orders' => 1,
                'total_spent' => 30.00,
            ],
            [
                'name_ar' => 'نورا محمود',
                'phone' => '+218989012345',
                'address_ar' => 'حي بو هديمة، بنغازي',
                'notes' => 'تطلب قطع توقيعية للمناسبات',
                'total_orders' => 10,
                'total_spent' => 500.00,
            ],
            [
                'name_ar' => 'يوسف طه',
                'phone' => '+218991234567',
                'address_ar' => 'شارع الرونق، مصراتة',
                'notes' => null,
                'total_orders' => 4,
                'total_spent' => 120.00,
            ],
            [
                'name_ar' => 'مريم عبدالرحمن',
                'phone' => '+218912340123',
                'address_ar' => 'حي السلماني، بنغازي',
                'notes' => 'عميلة متكررة - تفضل قطع التوقيع',
                'total_orders' => 18,
                'total_spent' => 540.00,
            ],
            [
                'name_ar' => 'عمر يوسف',
                'phone' => '+218912340124',
                'address_ar' => 'شارع دبي، بنغازي',
                'notes' => 'طلبات خاصة للمناسبات',
                'total_orders' => 6,
                'total_spent' => 300.00,
            ],
            [
                'name_ar' => 'ليلى أحمد',
                'phone' => '+218912340125',
                'address_ar' => 'حي الأندلس، بنغازي',
                'notes' => null,
                'total_orders' => 2,
                'total_spent' => 60.00,
            ],
            [
                'name_ar' => 'طارق سالم',
                'phone' => '+218912340126',
                'address_ar' => 'طريق المطار، بنغازي',
                'notes' => 'عميل نشط',
                'total_orders' => 14,
                'total_spent' => 420.00,
            ],
            [
                'name_ar' => 'دنيا خالد',
                'phone' => '+218912340127',
                'address_ar' => 'شارع عشرين، بنغازي',
                'notes' => null,
                'total_orders' => 7,
                'total_spent' => 210.00,
            ],
            [
                'name_ar' => 'مجدي علي',
                'phone' => '+218912340128',
                'address_ar' => 'حي المنصورة، بنغازي',
                'notes' => 'طلب حلويات للعيادة',
                'total_orders' => 9,
                'total_spent' => 270.00,
            ],
        ];

        foreach ($clients as $clientData) {
            Client::create([
                ...$clientData,
                'created_by' => $adminUser->id,
            ]);
        }
    }
}
