<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $posts = [
            [
                'topic_id' => 1,
                'title' => 'Xu hướng thiết kế phòng khách hiện đại năm 2025',
                'description' => 'Khám phá các xu hướng thiết kế phòng khách hiện đại, tối giản và sang trọng.',
                'content' => '
Phòng khách là không gian trung tâm của ngôi nhà, nơi tiếp đón khách và sinh hoạt chung của gia đình. 
Trong năm 2025, xu hướng thiết kế phòng khách hiện đại tập trung vào sự tối giản nhưng vẫn đảm bảo tính thẩm mỹ và tiện nghi.

Màu sắc chủ đạo thường là các gam màu trung tính như trắng, xám, be kết hợp với ánh sáng tự nhiên. 
Nội thất ưu tiên các đường nét gọn gàng, hạn chế chi tiết rườm rà.

Bên cạnh đó, việc sử dụng vật liệu thân thiện với môi trường như gỗ tự nhiên, đá hoặc vải tái chế 
đang ngày càng được ưa chuộng. Không gian mở kết nối phòng khách với phòng bếp cũng là xu hướng nổi bật.',
            ],
            [
                'topic_id' => 2,
                'title' => 'Cách bố trí phòng ngủ nhỏ gọn nhưng vẫn đầy đủ tiện nghi',
                'description' => 'Giải pháp tối ưu không gian cho phòng ngủ có diện tích nhỏ.',
                'content' => '
Phòng ngủ nhỏ đòi hỏi cách bố trí nội thất hợp lý để vừa đảm bảo công năng vừa tạo cảm giác thoải mái. 
Việc lựa chọn giường ngủ có ngăn kéo, tủ quần áo âm tường sẽ giúp tiết kiệm diện tích đáng kể.

Ngoài ra, màu sắc sáng như trắng, kem hoặc pastel giúp không gian trông rộng rãi hơn. 
Ánh sáng tự nhiên kết hợp đèn ngủ dịu nhẹ sẽ tạo cảm giác thư giãn.

Trang trí phòng ngủ nên tối giản, tránh sử dụng quá nhiều vật dụng không cần thiết để giữ không gian gọn gàng.',
            ],
            [
                'topic_id' => 3,
                'title' => 'Thiết kế phòng bếp tiện nghi cho căn hộ chung cư',
                'description' => 'Phòng bếp hiện đại cần kết hợp hài hòa giữa công năng và thẩm mỹ.',
                'content' => '
Phòng bếp là nơi giữ lửa cho gia đình, vì vậy thiết kế bếp cần đảm bảo sự tiện lợi và an toàn. 
Nguyên tắc tam giác bếp (bếp nấu – bồn rửa – tủ lạnh) giúp việc nấu nướng trở nên thuận tiện hơn.

Các căn hộ chung cư hiện nay thường ưu tiên tủ bếp chữ L hoặc chữ I để tiết kiệm diện tích. 
Chất liệu tủ bếp phổ biến là gỗ công nghiệp chống ẩm kết hợp mặt đá dễ lau chùi.

Ngoài ra, hệ thống hút mùi và ánh sáng đầy đủ là yếu tố không thể thiếu cho một căn bếp hiện đại.',
            ],
            [
                'topic_id' => 4,
                'title' => 'Thiết kế nội thất văn phòng giúp tăng hiệu suất làm việc',
                'description' => 'Không gian làm việc ảnh hưởng trực tiếp đến hiệu quả công việc.',
                'content' => '
Một văn phòng được thiết kế khoa học sẽ giúp nhân viên làm việc hiệu quả và sáng tạo hơn. 
Bàn ghế công thái học giúp giảm căng thẳng và bảo vệ sức khỏe lâu dài.

Không gian mở kết hợp cây xanh mang lại cảm giác thoải mái và tăng sự kết nối giữa các nhân viên. 
Màu sắc văn phòng thường sử dụng các gam màu trung tính kết hợp điểm nhấn để tránh sự nhàm chán.

Ánh sáng tự nhiên và hệ thống đèn chiếu sáng hợp lý cũng góp phần nâng cao hiệu suất làm việc.',
            ],
            [
                'topic_id' => 5,
                'title' => 'Trang trí nội thất bằng cây xanh – Xu hướng sống xanh',
                'description' => 'Cây xanh mang lại không gian sống trong lành và gần gũi thiên nhiên.',
                'content' => '
Trang trí nội thất bằng cây xanh đang trở thành xu hướng phổ biến trong các căn hộ hiện đại. 
Cây xanh không chỉ giúp thanh lọc không khí mà còn tạo điểm nhấn thẩm mỹ cho không gian sống.

Các loại cây như trầu bà, lưỡi hổ, kim tiền rất dễ chăm sóc và phù hợp với môi trường trong nhà. 
Bạn có thể đặt cây ở phòng khách, phòng làm việc hoặc ban công để tăng cảm giác thư giãn.

Kết hợp cây xanh với ánh sáng tự nhiên sẽ giúp không gian trở nên sinh động và tràn đầy sức sống.',
            ],
        ];

        foreach ($posts as $index => $post) {
            DB::table('post')->insert([
                'id' => $index + 1,
                'topic_id' => $post['topic_id'],
                'title' => $post['title'],
                'slug' => Str::slug($post['title']),
                'image' => '/uploads/posts/noithat_' . ($index + 1) . '.jpg',
                'content' => $post['content'],
                'description' => $post['description'],
                'post_type' => 'post',
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
