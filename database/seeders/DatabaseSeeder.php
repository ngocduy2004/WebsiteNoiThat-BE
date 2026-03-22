<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            Product_SaleSeeder::class,
            PromotionSeeder::class,
            SettingSeeder::class,
            PostSeeder::class,
            Order_ItemSeeder::class,
            TopicSeeder::class,
            OrderSeeder::class,
            ContactSeeder::class,
            BannerSeeder::class,
            MenuSeeder::class,
            Product_StoreSeeder::class,
            AttributesSeeder::class,
            Product_attributesSeeder::class,

        ]);
    }
}
