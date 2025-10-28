<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $betefourCategory = Category::where('prefix', 'BET')->first();
        $signatureCategory = Category::where('prefix', 'SIG')->first();
        $specialCategory = Category::where('prefix', 'SPL')->first();
        $adminUser = User::where('email', 'admin@kushaos.test')->first();

        $products = [
            // Betefour products
            [
                'name_ar' => 'برج بيتي فور صغير',
                'description_ar' => 'قطع بيتي فور صغيرة الحجم بنكهة الفانيليا',
                'category_id' => $betefourCategory->id,
                'base_price' => 15.00,
                'minimum_order_quantity' => 12,
            ],
            [
                'name_ar' => 'بيتي فور بالشوكولاتة',
                'description_ar' => 'بيتي فور مغطى بالشوكولاتة الداكنة',
                'category_id' => $betefourCategory->id,
                'base_price' => 18.00,
                'minimum_order_quantity' => 12,
            ],
            [
                'name_ar' => 'بيتي فور بالفستق',
                'description_ar' => 'بيتي فور محشو بكريمة الفستق الحلبي',
                'category_id' => $betefourCategory->id,
                'base_price' => 20.00,
                'minimum_order_quantity' => 12,
            ],
            [
                'name_ar' => 'بيتي فور باللوز',
                'description_ar' => 'بيتي فور محضر من طحين اللوز',
                'category_id' => $betefourCategory->id,
                'base_price' => 22.00,
                'minimum_order_quantity' => 12,
            ],
            [
                'name_ar' => 'بيتي فور بالكراميل',
                'description_ar' => 'بيتي فور محشو بكريمة الكراميل',
                'category_id' => $betefourCategory->id,
                'base_price' => 17.00,
                'minimum_order_quantity' => 12,
            ],
            [
                'name_ar' => 'بيتي فور بجوز الهند',
                'description_ar' => 'بيتي فور مغطى بجوز الهند المحمص',
                'category_id' => $betefourCategory->id,
                'base_price' => 16.00,
                'minimum_order_quantity' => 12,
            ],
            [
                'name_ar' => 'بيتي فور بالقهوة',
                'description_ar' => 'بيتي فور بنكهة القهوة العربية',
                'category_id' => $betefourCategory->id,
                'base_price' => 19.00,
                'minimum_order_quantity' => 12,
            ],
            [
                'name_ar' => 'بيتي فور بالليمون',
                'description_ar' => 'بيتي فور منعش بنكهة الليمون',
                'category_id' => $betefourCategory->id,
                'base_price' => 16.00,
                'minimum_order_quantity' => 12,
            ],
            [
                'name_ar' => 'بيتي فور بالفراولة',
                'description_ar' => 'بيتي فور محشو بمربى الفراولة الطبيعي',
                'category_id' => $betefourCategory->id,
                'base_price' => 17.50,
                'minimum_order_quantity' => 12,
            ],
            [
                'name_ar' => 'بيتي فور ملون',
                'description_ar' => 'تشكيلة ملونة من البيتي فور',
                'category_id' => $betefourCategory->id,
                'base_price' => 18.50,
                'minimum_order_quantity' => 24,
            ],
            [
                'name_ar' => 'بيتي فور بالعسل',
                'description_ar' => 'بيتي فور محلى بالعسل الطبيعي',
                'category_id' => $betefourCategory->id,
                'base_price' => 21.00,
                'minimum_order_quantity' => 12,
            ],
            [
                'name_ar' => 'بيتي فور بالزعفران',
                'description_ar' => 'بيتي فور فاخر بنكهة الزعفران',
                'category_id' => $betefourCategory->id,
                'base_price' => 25.00,
                'minimum_order_quantity' => 6,
            ],

            // Signature items
            [
                'name_ar' => 'كوشة سبيشل',
                'description_ar' => 'قطعة مميزة من ابتكار المخبز',
                'category_id' => $signatureCategory->id,
                'base_price' => 30.00,
                'minimum_order_quantity' => 6,
            ],
            [
                'name_ar' => 'ميني تارت بالمكسرات',
                'description_ar' => 'تارت صغير محشو بخليط المكسرات الفاخرة',
                'category_id' => $signatureCategory->id,
                'base_price' => 28.00,
                'minimum_order_quantity' => 6,
            ],
            [
                'name_ar' => 'غريبة ليبية فاخرة',
                'description_ar' => 'غريبة تقليدية بوصفة خاصة',
                'category_id' => $signatureCategory->id,
                'base_price' => 22.00,
                'minimum_order_quantity' => 12,
            ],

            // Special orders
            [
                'name_ar' => 'طلب خاص - بيتي فور',
                'description_ar' => 'طلب مخصص من البيتي فور حسب رغبة العميل',
                'category_id' => $specialCategory->id,
                'base_price' => 25.00,
                'minimum_order_quantity' => 1,
                'allow_below_minimum' => true,
            ],
            [
                'name_ar' => 'طلب خاص - حلويات',
                'description_ar' => 'حلويات مخصصة للمناسبات',
                'category_id' => $specialCategory->id,
                'base_price' => 35.00,
                'minimum_order_quantity' => 1,
                'allow_below_minimum' => true,
            ],

            // Additional betefour varieties
            [
                'name_ar' => 'بيتي فور بالبندق',
                'description_ar' => 'بيتي فور محشو بكريمة البندق',
                'category_id' => $betefourCategory->id,
                'base_price' => 19.50,
                'minimum_order_quantity' => 12,
            ],
            [
                'name_ar' => 'بيتي فور بالتمر',
                'description_ar' => 'بيتي فور محشو بعجينة التمر',
                'category_id' => $betefourCategory->id,
                'base_price' => 17.00,
                'minimum_order_quantity' => 12,
            ],
            [
                'name_ar' => 'بيتي فور بالورد',
                'description_ar' => 'بيتي فور بنكهة ماء الورد',
                'category_id' => $betefourCategory->id,
                'base_price' => 18.00,
                'minimum_order_quantity' => 12,
            ],
        ];

        foreach ($products as $productData) {
            Product::create([
                ...$productData,
                'is_available' => true,
                'allow_below_minimum' => $productData['allow_below_minimum'] ?? false,
                'created_by' => $adminUser->id,
            ]);
        }
    }
}
