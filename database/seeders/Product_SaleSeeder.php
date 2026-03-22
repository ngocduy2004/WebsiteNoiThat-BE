<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Product_SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          $now = Carbon::now();

        for ($i = 1; $i <= 10; $i++) {
            $date_begin = $now->copy()->subDays(rand(1, 10));
            $date_end = $date_begin->copy()->addDays(rand(5, 20));

            DB::table('product_sale')->insert([
                'id'         => $i,
                'name'       => 'Khuyến mãi Sản phẩm ' . $i,
                'date_begin' => $date_begin,
                'date_end'   => $date_end,
                'created_by' => 1,
                'updated_by' => 1,
                'status'     => rand(0,1),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
