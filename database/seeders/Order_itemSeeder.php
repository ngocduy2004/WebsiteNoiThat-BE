<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Order_itemSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $items = [
            [
                'id' => 1,
                'order_id' => 1,
                'product_id' => 1,
                'price' => 1200000,
                'qty' => 1,
                'discount' => 10,
            ],
            [
                'id' => 2,
                'order_id' => 1,
                'product_id' => 2,
                'price' => 850000,
                'qty' => 2,
                'discount' => 0,
            ],
            [
                'id' => 3,
                'order_id' => 2,
                'product_id' => 3,
                'price' => 450000,
                'qty' => 1,
                'discount' => 5,
            ],
            [
                'id' => 4,
                'order_id' => 3,
                'product_id' => 4,
                'price' => 2300000,
                'qty' => 1,
                'discount' => 15,
            ],
            [
                'id' => 5,
                'order_id' => 4,
                'product_id' => 5,
                'price' => 670000,
                'qty' => 3,
                'discount' => 0,
            ],
            [
                'id' => 6,
                'order_id' => 5,
                'product_id' => 6,
                'price' => 990000,
                'qty' => 2,
                'discount' => 20,
            ],
        ];

        foreach ($items as $item) {
            $amount = ($item['price'] * $item['qty']) * (1 - $item['discount'] / 100);

            DB::table('order_item')->insert([
                'id' => $item['id'],
                'order_id' => $item['order_id'],
                'product_id' => $item['product_id'],
                'price' => $item['price'],
                'qty' => $item['qty'],
                'discount' => $item['discount'],
                'amount' => $amount,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
