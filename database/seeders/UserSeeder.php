<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $users = [
            [
                'id' => 1,
                'name' => 'Nguyễn Ngọc Duy',
                'email' => 'admin@gmail.com',
                'phone' => '0901000001',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'roles' => 'admin',
                'avatar' => '/uploads/avatars/avatar1.png',
                'status' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Nguyễn Văn User',
                'email' => 'user@gmail.com',
                'phone' => '0901000002',
                'username' => 'user',
                'password' => Hash::make('user123'),
                'roles' => 'user',
                'avatar' => '/uploads/avatars/avatar2.png',
                'status' => 1,
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->insert(array_merge($user, [
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
