<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attribute;

class AttributesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $attributes = [
            ['name' => 'Màu sắc'],
            ['name' => 'Kích thước'],
        ];

        Attribute::insert($attributes);
    }
}
