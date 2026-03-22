<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Banner::create([
                'name' => "Banner số $i",
                'image' => "/uploads/banners/banner_$i.jpg",
                'link' => "https://example.com/banner-$i",
                'position' => $i % 2 == 0 ? 'slideshow' : 'ads', // phải đúng ENUM
                'sort_order' => $i,
                'description' => "Mô tả cho banner số $i",
                'created_by' => 1,
                'updated_by' => 1,
                'status' => $i % 3 === 0 ? 0 : 1,
            ]);
        }
    }
}
