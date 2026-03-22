<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductAttribute;

class Product_attributesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProductAttribute::create([
            'product_id' => 1,
            'attribute_id' => 1, // Màu sắc
            'value' => 'Trắng'
        ]);

        ProductAttribute::create([
            'product_id' => 1,
            'attribute_id' => 2, // Kích thước
            'value' => '120×75×75'
        ]);

        ProductAttribute::create([
            'product_id' => 2,
            'attribute_id' => 1,
            'value' => 'Nâu'
        ]);

        ProductAttribute::create([
            'product_id' => 2,
            'attribute_id' => 2,
            'value' => '80×80×85'
        ]);

        ProductAttribute::create([
            'product_id' => 3,
            'attribute_id' => 1,
            'value' => 'Đen'
        ]);

        ProductAttribute::create([
            'product_id' => 3,
            'attribute_id' => 2,
            'value' => '100×200'
        ]);
    }
}
