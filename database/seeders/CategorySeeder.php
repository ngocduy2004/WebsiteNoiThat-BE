<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        /**
         * =========================
         * DANH MỤC CHA
         * =========================
         */
        $parents = [
            [
                'name' => 'Phòng khách',
                'image' => 'living_room.jpg',
                'description' => 'Danh mục nội thất phòng khách',
            ],
            [
                'name' => 'Phòng ngủ',
                'image' => 'bedroom.jpg',
                'description' => 'Danh mục nội thất phòng ngủ',
            ],
               [
                'name' => 'Phòng ăn',
                'image' => 'dining_room.jpg',
                'description' => 'Danh mục nội thất phòng ăn',
            ],
            [
                'name' => 'Văn phòng',
                'image' => 'office.jpg',
                'description' => 'Danh mục nội thất văn phòng',
            ],
          
        ];

        foreach ($parents as $index => $parent) {
            DB::table('categories')->insert([
                'name'        => $parent['name'],
                'slug'        => Str::slug($parent['name']),
                'image'       => $parent['image'],
                'parent_id'   => null, // CHA
                'sort_order'  => $index + 1,
                'description' => $parent['description'],
                'created_by'  => 1,
                'updated_by'  => 1,
                'status'      => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        /**
         * =========================
         * DANH MỤC CON
         * =========================
         */
        $children = [
            'Phòng khách' => [
                'Sofa',
                'Bàn trà',
                'Kệ tivi',
            ],
            'Phòng ngủ' => [
                'Giường',
                'Tủ quần áo',
                'Bàn trang điểm',
            ],
            'Phòng ăn' => [
                'Ghế ăn',
                'Bàn ăn',
                'Bộ bàn ghế ăn',
                
            ],
            'Văn phòng' => [
                'Bàn làm việc',
                'Ghế văn phòng',
                'Kệ sách',
            ],
        ];

        foreach ($children as $parentName => $childList) {
            $parentId = DB::table('categories')
                ->where('name', $parentName)
                ->value('id');

            foreach ($childList as $index => $childName) {
                DB::table('categories')->insert([
                    'name'        => $childName,
                    'slug'        => Str::slug($childName),
                    'image'       => 'child.jpg',
                    'parent_id'   => $parentId, // CON
                    'sort_order'  => $index + 1,
                    'description' => "Danh mục $childName",
                    'created_by'  => 1,
                    'updated_by'  => 1,
                    'status'      => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
