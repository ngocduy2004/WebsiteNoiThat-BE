<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $products = [
            [
                'category_id' => 1, // Sofa
                'name' => 'Sofa da phòng khách cao cấp',
                'slug' => Str::slug('Sofa da phòng khách cao cấp'),
                'thumbnail' => 'sofa-da-phong-khach.jpg',
                'content' => 'Sofa da thật cao cấp, khung gỗ sồi tự nhiên, thiết kế hiện đại.',
                'description' => 'Sofa da cao cấp cho phòng khách sang trọng.',
                'price_buy' => 18500000,
                'status' => 1,
            ],
            [
                'category_id' => 2, // Bàn ăn
                'name' => 'Bàn ăn gỗ tự nhiên 6 ghế',
                'slug' => Str::slug('Bàn ăn gỗ tự nhiên 6 ghế'),
                'thumbnail' => 'ban-an-go-tu-nhien.jpg',
                'content' => 'Bàn ăn gỗ sồi tự nhiên, chống mối mọt, độ bền cao.',
                'description' => 'Bàn ăn gỗ 6 ghế cho gia đình.',
                'price_buy' => 12500000,
                'status' => 1,
            ],
            [
                'category_id' => 3, // Giường ngủ
                'name' => 'Giường ngủ gỗ MDF chống ẩm',
                'slug' => Str::slug('Giường ngủ gỗ MDF chống ẩm'),
                'thumbnail' => 'giuong-ngu-go-mdf.jpg',
                'content' => 'Giường ngủ MDF phủ melamine, thiết kế tối giản.',
                'description' => 'Giường ngủ hiện đại, phù hợp căn hộ chung cư.',
                'price_buy' => 8900000,
                'status' => 1,
            ],
            [
                'category_id' => 4, // Tủ quần áo
                'name' => 'Tủ quần áo 4 cánh gỗ công nghiệp',
                'slug' => Str::slug('Tủ quần áo 4 cánh gỗ công nghiệp'),
                'thumbnail' => 'tu-quan-ao-4-canh.jpg',
                'content' => 'Tủ quần áo gỗ công nghiệp MDF, nhiều ngăn tiện lợi.',
                'description' => 'Tủ quần áo 4 cánh rộng rãi.',
                'price_buy' => 10500000,
                'status' => 1,
            ],
            [
                'category_id' => 5, // Kệ TV
                'name' => 'Kệ tivi phòng khách hiện đại',
                'slug' => Str::slug('Kệ tivi phòng khách hiện đại'),
                'thumbnail' => 'ke-tivi-phong-khach.jpg',
                'content' => 'Kệ tivi gỗ MDF phủ veneer, kiểu dáng hiện đại.',
                'description' => 'Kệ tivi cho phòng khách sang trọng.',
                'price_buy' => 6500000,
                'status' => 1,
            ],
        ];

        foreach ($products as $product) {
            DB::table('products')->insert(array_merge($product, [
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
