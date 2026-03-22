<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
           $now = Carbon::now();

        for ($i = 1; $i <= 10; $i++) {
            $discount_type = $i % 2 == 0 ? 'percent' : 'amount';
            $discount_value = $discount_type == 'percent' ? rand(5, 50) : rand(10000, 500000);
            $date_begin = $now->copy()->subDays(rand(1, 10));
            $date_end = $date_begin->copy()->addDays(rand(5, 20));

            DB::table('promotion')->insert([
                'id'             => $i,
                'name'           => 'Khuyến mãi ' . $i,
                'code'           => 'PROMO' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'description'    => 'Mô tả khuyến mãi ' . $i,
                'discount_type'  => $discount_type,
                'discount_value' => $discount_value,
                'date_begin'     => $date_begin,
                'date_end'       => $date_end,
                'created_by'     => 1,
                'updated_by'     => 1,
                'status'         => rand(0, 1),
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }
}
