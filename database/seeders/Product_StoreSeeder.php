<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Product_StoreSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $stores = [
            [
                'product_id' => 1, // Sofa da
                'price_root' => 14000000, // giá nhập
                'qty'        => 8,
                'status'     => 1,
            ],
            [
                'product_id' => 2, // Bàn ăn gỗ
                'price_root' => 9500000,
                'qty'        => 12,
                'status'     => 1,
            ],
            [
                'product_id' => 3, // Giường ngủ
                'price_root' => 6500000,
                'qty'        => 10,
                'status'     => 1,
            ],
            [
                'product_id' => 4, // Tủ quần áo
                'price_root' => 8000000,
                'qty'        => 6,
                'status'     => 1,
            ],
            [
                'product_id' => 5, // Kệ tivi
                'price_root' => 4500000,
                'qty'        => 15,
                'status'     => 1,
            ],
        ];

        foreach ($stores as $store) {
            DB::table('product_store')->insert([
                'product_id' => $store['product_id'],
                'price_root' => $store['price_root'],
                'qty'        => $store['qty'],
                'status'     => $store['status'],
                'created_at' => $now,
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
            ]);
        }
    }
}
