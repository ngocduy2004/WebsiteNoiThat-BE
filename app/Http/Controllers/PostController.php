<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request)
    {
        // Khởi tạo query và Eager Load quan hệ 'topic' để lấy tên chủ đề
        $query = Post::with('topic');

        // ============================
        // 1. LỌC THEO TRẠNG THÁI (Mới)
        // ============================
        // Nếu frontend gửi lên status thì lọc, nếu không thì mặc định lấy tất cả (cho admin)
        // Hoặc bạn có thể mặc định chỉ lấy status=1 nếu là API public
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // ============================
        // 2. LỌC THEO TOPIC_ID (Mới)
        // ============================
        if ($request->has('topic_id') && $request->topic_id != "") {
            $query->where('topic_id', $request->topic_id);
        }

        // Lọc theo Topic Slug (Nếu URL dùng slug)
        if ($request->has('topic_slug') && $request->topic_slug != "") {
            $slug = $request->topic_slug;
            $query->whereHas('topic', function ($q) use ($slug) {
                $q->where('slug', $slug);
            });
        }

        // ============================
        // 3. SEARCH (Giữ nguyên)
        // ============================
        if ($request->has('search') && $request->search != "") {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Đếm tổng sau khi đã lọc (để phân trang chính xác)
        $total = $query->count();

        // ============================
        // 4. SORT & PAGINATION
        // ============================
        $query->orderBy('created_at', 'desc');

        // Phân trang
        $limit = $request->limit ?? 10;
        $page = $request->page ?? 1;
        $offset = ($page - 1) * $limit;

        $posts = $query->offset($offset)->limit($limit)->get();

        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách bài viết thành công',
            'data' => $posts,
            'meta' => [
                'total' => $total,
                'limit' => (int) $limit,
                'page' => (int) $page,
                'total_pages' => ceil($total / $limit)
            ]
        ], 200);
    }
    // =========================
    // STORE
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'topic_id' => 'required|integer|exists:topic,id', // Topic ID phải tồn tại trong bảng topic
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Validate FILE ẢNH
            'content' => 'required|string',
            'description' => 'nullable|string',
            'post_type' => 'required|integer',
            'status' => 'required|integer',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            // Lưu file vào storage/app/public/posts
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        $data = Post::create([
            'topic_id' => $request->topic_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'image' => $imagePath, // Lưu đường dẫn ảnh
            'content' => $request->input('content'),
            'description' => $request->description,
            'post_type' => $request->post_type,
            'status' => $request->status,
            'created_at' => now(),
            'created_by' => Auth::id() ?? 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Tạo bài viết thành công',
            'data' => $data,
        ], 200);
    }
    // =========================
    // SHOW DETAIL
    // =========================
    public function show($id)
    {
        // Sửa: Tìm theo ID hoặc Slug
        $post = Post::with('topic')
            ->where('id', $id)
            ->orWhere('slug', $id)
            ->first();

        if (!$post) {
            return response()->json([
                'status' => false,
                'message' => 'Bài viết không tồn tại',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $post,
        ], 200);
    }
    // =========================
    // UPDATE
    // =========================
    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        $request->validate([
            'topic_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048', // Validate file ảnh
            // ... các validate khác
        ]);

        // Xử lý ảnh
        $imagePath = $post->image; // Giữ ảnh cũ
        if ($request->hasFile('image')) {
            // 1. Xóa ảnh cũ nếu cần (Optional)
            // if ($post->image && Storage::exists('public/' . $post->image)) {
            //    Storage::delete('public/' . $post->image);
            // }

            // 2. Lưu ảnh mới
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        $post->update([
            'topic_id' => $request->topic_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'image' => $imagePath, // Cập nhật đường dẫn mới
            'content' => $request->input('content'),
            'description' => $request->description,
            'post_type' => $request->post_type,
            'status' => $request->status,
            'updated_at' => now(),
            'updated_by' => Auth::id() ?? 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật thành công',
            'data' => $post
        ], 200);
    }

    // =========================
    // DELETE
    // =========================
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'status' => false,
                'message' => 'Bài viết không tồn tại',
            ], 404);
        }

        $post->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa bài viết thành công',
        ], 200);
    }
}
