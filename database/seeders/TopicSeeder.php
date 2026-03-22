<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TopicSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $topics = [
            'Phòng khách',
            'Phòng ngủ',
            'Phòng bếp',
            'Văn phòng',
            'Trang trí nội thất',
        ];

        foreach ($topics as $index => $name) {
            DB::table('topic')->insert([
                'id'          => $index + 1,
                'name'        => $name,
                'slug'        => Str::slug($name),
                'sort_order'  => $index + 1,
                'description' => 'Các bài viết về nội thất ' . strtolower($name),
                'created_by'  => 1,
                'updated_by'  => 1,
                'status'      => 1, // luôn active
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }
}
