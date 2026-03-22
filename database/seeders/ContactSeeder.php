<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contact;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Contact::create([
                'user_id' => rand(1, 10),
                'name' => "User $i",
                'email' => "user$i@gmail.com",
                'phone' => "0900".rand(100000,999999),
                'content' => "Nội dung liên hệ số $i",
                'reply_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => rand(0, 1),
            ]);
        }
    }
}
