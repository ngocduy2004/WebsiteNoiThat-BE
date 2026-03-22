<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $orders = [
            [
                'id' => 1,
                'user_id' => 1,
                'name' => 'Nguyễn Văn A',
                'email' => 'vana@gmail.com',
                'phone' => '0901234567',
                'address' => '12 Nguyễn Trãi, Hà Nội',
                'note' => 'Giao giờ hành chính',
                'status' => 3, // ✅ Giao hàng thành công
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'name' => 'Trần Thị B',
                'email' => 'thib@gmail.com',
                'phone' => '0902345678',
                'address' => '45 Lê Lợi, Hà Nội',
                'note' => 'Giao buổi tối',
                'status' => 2, // 🚚 Đang giao hàng
            ],
            [
                'id' => 3,
                'user_id' => 3,
                'name' => 'Lê Văn C',
                'email' => 'vanc@gmail.com',
                'phone' => '0903456789',
                'address' => '78 Trường Chinh, Hà Nội',
                'note' => 'Liên hệ trước khi giao',
                'status' => 1, // ✔ Đã xác nhận
            ],
            [
                'id' => 4,
                'user_id' => 4,
                'name' => 'Phạm Thị D',
                'email' => 'thid@gmail.com',
                'phone' => '0904567890',
                'address' => '90 Giải Phóng, Hà Nội',
                'note' => 'Không có ghi chú',
                'status' => 0, // 🕒 Chờ xác nhận
            ],
            [
                'id' => 5,
                'user_id' => 5,
                'name' => 'Hoàng Văn E',
                'email' => 'vane@gmail.com',
                'phone' => '0905678901',
                'address' => '15 Cầu Giấy, Hà Nội',
                'note' => 'Giao nhanh',
                'status' => 4, // ❌ Đã hủy
            ],
        ];

        foreach ($orders as $order) {
            DB::table('order')->insert(array_merge($order, [
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
