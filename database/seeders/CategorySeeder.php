<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'اكسسوارات',
                'description' => 'مختلف أنواع الاكسسوارات والهدايا',
                'subcategories' => [
                    [
                        'name' => 'ساعة',
                        'products' => [
                            ['name' => 'ساعة كلاسيك رجالي', 'purchase_price' => 350, 'selling_price' => 550],
                            ['name' => 'ساعة ستانلس حريمي', 'purchase_price' => 450, 'selling_price' => 700],
                        ],
                    ],
                    [
                        'name' => 'سلسلة',
                        'products' => [
                            ['name' => 'سلسلة فضي بسيطة', 'purchase_price' => 120, 'selling_price' => 200],
                            ['name' => 'سلسلة ستانلس مطلية', 'purchase_price' => 180, 'selling_price' => 300],
                        ],
                    ],
                    [
                        'name' => 'خاتم',
                        'products' => [
                            ['name' => 'خاتم ستانلس رجالي', 'purchase_price' => 100, 'selling_price' => 180],
                            ['name' => 'خاتم حريمي بسيط', 'purchase_price' => 80, 'selling_price' => 150],
                        ],
                    ],
                ],
            ],

            [
                'name' => 'ميكب اب',
                'description' => 'مستحضرات ومنتجات الميكب',
                'subcategories' => [
                    [
                        'name' => 'روج',
                        'products' => [
                            ['name' => 'روج مات أحمر', 'purchase_price' => 80, 'selling_price' => 130],
                            ['name' => 'روج نود', 'purchase_price' => 90, 'selling_price' => 150],
                        ],
                    ],
                    [
                        'name' => 'ماسكارا',
                        'products' => [
                            ['name' => 'ماسكارا سوداء', 'purchase_price' => 100, 'selling_price' => 170],
                            ['name' => 'ماسكارا ضد الماء', 'purchase_price' => 120, 'selling_price' => 200],
                        ],
                    ],
                    [
                        'name' => 'كحل',
                        'products' => [
                            ['name' => 'كحل أسود', 'purchase_price' => 50, 'selling_price' => 90],
                            ['name' => 'كحل ضد الماء', 'purchase_price' => 65, 'selling_price' => 110],
                        ],
                    ],
                ],
            ],

            [
                'name' => 'بوكسات',
                'description' => 'بوكسات هدايا متنوعة',
                'subcategories' => [
                    [
                        'name' => 'بوكس عيد ميلاد',
                        'products' => [
                            ['name' => 'بوكس عيد ميلاد صغير', 'purchase_price' => 250, 'selling_price' => 400],
                            ['name' => 'بوكس عيد ميلاد كبير', 'purchase_price' => 450, 'selling_price' => 700],
                        ],
                    ],
                    [
                        'name' => 'بوكس هدايا',
                        'products' => [
                            ['name' => 'بوكس هدايا كلاسيك', 'purchase_price' => 300, 'selling_price' => 500],
                            ['name' => 'بوكس هدايا فاخر', 'purchase_price' => 500, 'selling_price' => 800],
                        ],
                    ],
                    [
                        'name' => 'بوكس مناسبات',
                        'products' => [
                            ['name' => 'بوكس مناسبة صغير', 'purchase_price' => 350, 'selling_price' => 550],
                            ['name' => 'بوكس مناسبة فاخر', 'purchase_price' => 600, 'selling_price' => 950],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {

            $category = Category::create([
                'name' => $categoryData['name'],
                'description' => $categoryData['description'],
                'is_active' => true,
            ]);

            foreach ($categoryData['subcategories'] as $subcategoryData) {

                $subcategory = Subcategory::create([
                    'category_id' => $category->id,
                    'name' => $subcategoryData['name'],
                    'description' => null,
                    'is_active' => true,
                ]);

                foreach ($subcategoryData['products'] as $productData) {

                    Product::create([
                        'subcategory_id' => $subcategory->id,
                        'name' => $productData['name'],
                        'description' => null,
                        'purchase_price' => $productData['purchase_price'],
                        'selling_price' => $productData['selling_price'],
                        'stock_quantity' => rand(10, 50),
                        'minimum_stock' => 5,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
