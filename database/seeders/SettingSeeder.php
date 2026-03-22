<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $now = Carbon::now();

        for ($i = 1; $i <= 5; $i++) {
            DB::table('settings')->insert([
                'id'         => $i,
                'site_name'  => 'Website mẫu ' . $i,
                'email'      => 'contact' . $i . '@example.com',
                'phone'      => '090' . rand(1000000, 9999999),
                'hotline'    => '1800' . rand(100000, 999999),
                'address'    => 'Địa chỉ ' . $i . ', Hà Nội',
                'status'     => rand(0, 1),
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }
    }
}
