<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; 

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $query = Banner::query();

        // 🔍 Search theo tên
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // ✅ LỌC THEO STATUS (QUAN TRỌNG)
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // ✅ LỌC THEO POSITION (QUAN TRỌNG)
        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        $total = $query->count();

        // 📄 Pagination
        if ($request->has('limit') && $request->has('page')) {
            $limit = (int) $request->limit;
            $page = (int) $request->page;
            $offset = ($page - 1) * $limit;
            $query->offset($offset)->limit($limit);
        } elseif ($request->has('limit')) {
            $query->limit((int) $request->limit);
        }

        $banners = $query
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $banners,
            'total' => $total,
            'message' => 'Lấy danh sách banner thành công',
        ]);
    }

    // =========================
    // STORE (THÊM MỚI) - ĐÃ SỬA UPLOAD ẢNH
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // ⚠️ Validate file ảnh
            'link' => 'nullable|string|max:255',
            'position' => 'required|in:slideshow,ads',
            'status' => 'required|integer|in:0,1',
        ]);

        $imagePath = null;

        // 👇 LOGIC UPLOAD ẢNH TRỰC TIẾP VÀO PUBLIC
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();

            // Lưu vào thư mục: public/uploads/banners
            $file->move(public_path('uploads/banners'), $filename);

            // Đường dẫn lưu vào DB (ngắn gọn)
            $imagePath = 'uploads/banners/' . $filename;
        }

        $banner = Banner::create([
            'name' => $request->name,
            'image' => $imagePath, // Lưu đường dẫn
            'link' => $request->link,
            'position' => $request->position,
            'sort_order' => $request->sort_order ?? 0,
            'description' => $request->description,
            'status' => $request->status,
            'created_at' => now(),
            'created_by' => Auth::id() ?? 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Thêm banner thành công',
            'data' => $banner,
        ], 200);
    }

    // =========================
    // UPDATE - ĐÃ SỬA UPLOAD ẢNH
    // =========================
    public function update(Request $request, $id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json(['status' => false, 'message' => 'Banner không tồn tại'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // Update thì ảnh có thể null (không đổi ảnh)
            'link' => 'nullable|string|max:255',
            'position' => 'required|in:slideshow,ads',
            'status' => 'required|integer|in:0,1',
        ]);

        $dataUpdate = [
            'name' => $request->name,
            'link' => $request->link,
            'position' => $request->position,
            'sort_order' => $request->sort_order ?? $banner->sort_order,
            'description' => $request->description,
            'status' => $request->status,
            'updated_at' => now(),
            'updated_by' => Auth::id() ?? 1,
        ];

        // 👇 LOGIC CẬP NHẬT ẢNH MỚI
        if ($request->hasFile('image')) {
            // 1. Xóa ảnh cũ nếu có
            if ($banner->image && File::exists(public_path($banner->image))) {
                File::delete(public_path($banner->image));
            }

            // 2. Upload ảnh mới
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/banners'), $filename);

            $dataUpdate['image'] = 'uploads/banners/' . $filename;
        }

        $banner->update($dataUpdate);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật banner thành công',
        ], 200);
    }

    // =========================
    // DELETE - ĐÃ SỬA ĐỂ XÓA FILE ẢNH
    // =========================
    public function destroy($id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json(['status' => false, 'message' => 'Banner không tồn tại'], 404);
        }

        // Xóa file ảnh trong thư mục public
        if ($banner->image && File::exists(public_path($banner->image))) {
            File::delete(public_path($banner->image));
        }

        $banner->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa banner thành công',
        ], 200);
    }

    // =========================
    // SHOW DETAIL
    // =========================
    public function show($id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json([
                'status' => false,
                'message' => 'Banner không tồn tại',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $banner,
        ], 200);
    }
    
}
// =========================
// UPDATE
// =========================
// public function update(Request $request, $id)
// {
//     $banner = Banner::find($id);

//     if (!$banner) {
//         return response()->json([
//             'status' => false,
//             'message' => 'Banner không tồn tại',
//         ], 404);
//     }

//     $request->validate([
//        'name' => 'required|string|max:255',
//         'image' => 'required|string|max:255',
//         'link' => 'nullable|string|max:255',
//         'position' => 'required|in:slideshow,ads', // ⭐ QUAN TRỌNG
//         'sort_order' => 'nullable|integer',
//         'description' => 'nullable|string',
//         'status' => 'required|integer|in:0,1',
//     ]);

//     $banner->update([
//         'name' => $request->name,
//         'image' => $request->image,
//         'link' => $request->link,
//         'position' => $request->position,
//         'sort_order' => $request->sort_order ?? $banner->sort_order,
//         'description' => $request->description,
//         'status' => $request->status,
//         'updated_at' => now(),
//         'updated_by' => Auth::id() ?? 1,
//     ]);

//     return response()->json([
//         'status' => true,
//         'message' => 'Cập nhật banner thành công',
//     ], 200);
// }

// =========================
// DELETE
// =========================
//     public function destroy($id)
//     {
//         $banner = Banner::find($id);

//         if (!$banner) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'Banner không tồn tại',
//             ], 404);
//         }

//         $banner->delete();

//         return response()->json([
//             'status' => true,
//             'message' => 'Xóa banner thành công',
//         ], 200);
//     }
// }