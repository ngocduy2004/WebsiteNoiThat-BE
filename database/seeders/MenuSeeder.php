<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $types = ['category', 'page', 'topic', 'custom'];
        $positions = ['mainmenu', 'footermenu'];

        $menuCount = 10; 

        for ($i = 1; $i <= $menuCount; $i++) {

            // 50% cơ hội tạo menu con
            $parent = $i > 3 && rand(0, 1) ? rand(1, $i - 1) : 0;

            DB::table('menu')->insert([
                'name'       => 'Menu ' . $i,
                'link'       => '/menu-' . $i,
                'type'       => $types[array_rand($types)],
                'parent_id'  => $parent,
                'sort_order' => $i,
                'table_id'   => rand(1, 10),
                'position'   => $positions[array_rand($positions)],
                'created_by' => 1,
                'updated_by' => 1,
                'status'     => rand(0, 1),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
