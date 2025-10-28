<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name_en' => 'Betefour',
                'name_ar' => 'بيتفور',
                'prefix' => 'BET',
                'description_ar' => 'قطع البيتفور التقليدية',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name_en' => 'Signature Items',
                'name_ar' => 'قطع توقيعية',
                'prefix' => 'SIG',
                'description_ar' => 'القطع المميزة والمبتكرة الخاصة بالمخبز',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name_en' => 'Special Orders',
                'name_ar' => 'طلبات خاصة',
                'prefix' => 'SPL',
                'description_ar' => 'طلبات مخصصة حسب رغبة العميل',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name_en' => 'Other',
                'name_ar' => 'أخرى',
                'prefix' => 'OTH',
                'description_ar' => 'منتجات متنوعة أخرى',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
