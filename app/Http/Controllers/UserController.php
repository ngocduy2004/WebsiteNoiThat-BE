<?php

namespace App\Http\Controllers;
use App\Models\UserCustomer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = UserCustomer::query();

        // if ($request->has('roles')) {
        //     $query->where('roles', $request->roles);
        // }
        // Tìm kiếm theo tên hoặc mã
        if ($request->has('search') && $request->search != "") {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%');
        }

        $total = UserCustomer::count();


        if ($request->has('limit') && $request->has('page')) {
            $limit = $request->input('limit');
            $page = $request->input('page');
            $offset = ($page - 1) * $limit;

            $query->offset($offset)->limit($limit);
        } else {

            if ($request->has('limit')) {
                $limit = $request->limit;
                $query->limit($limit);
            }
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => true,
            'data' => $users,
            'total' => $total,
            'message' => 'Lấy danh sách người dùng thành công',
        ]);
    }

    // =========================
    // STORE
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6',
            'roles' => 'required|string|max:50',
            'avatar' => 'nullable|string|max:255',
            'status' => 'required|integer|in:0,1',
        ]);

        $user = UserCustomer::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'roles' => $request->roles,
            'avatar' => $request->avatar,
            'status' => $request->status,
            'created_at' => now(),
            'created_by' => Auth::id() ?? 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Tạo người dùng thành công',
            'data' => $user,
        ], 200);
    }

    // =========================
    // SHOW
    // =========================
    public function show($id)
    {
        $user = UserCustomer::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Người dùng không tồn tại',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $user,
        ], 200);
    }

    // =========================
    // UPDATE
    // =========================
    public function update(Request $request, $id)
    {
        $user = UserCustomer::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Người dùng không tồn tại',
            ], 404);
        }

        // Validate
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|max:255|unique:users,email,$id",
            'phone' => 'nullable|string|max:20',
            'username' => "required|string|max:255|unique:users,username,$id",
            'password' => 'nullable|string|min:6',
            'avatar' => 'nullable',
            'status' => 'required|integer|in:0,1',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'username' => $request->username,
            'roles' => $request->roles ?? $user->roles,
            'status' => $request->status,
            'updated_at' => now(),
            'updated_by' => Auth::id() ?? 1,
        ];

        // --- XỬ LÝ FILE ẢNH AN TOÀN (SỬA ĐOẠN NÀY) ---
        if ($request->hasFile('avatar')) {
            try {
                $file = $request->file('avatar');

                // 1. Kiểm tra và tạo thư mục nếu chưa có
                $uploadPath = public_path('uploads/avatars');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                // 2. Xóa ảnh cũ (Bọc trong try-catch để lỡ lỗi cũng không chết chương trình)
                if ($user->avatar) {
                    $oldPath = public_path($user->avatar);
                    // Chỉ xóa nếu file tồn tại và ĐÓ LÀ FILE (không phải thư mục)
                    if (file_exists($oldPath) && is_file($oldPath)) {
                        @unlink($oldPath); // Dấu @ để bỏ qua lỗi warning nếu có
                    }
                }

                // 3. Tạo tên file mới
                $filename = uniqid() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

                // 4. Di chuyển file
                $file->move($uploadPath, $filename);

                // 5. Lưu đường dẫn
                $data['avatar'] = 'uploads/avatars/' . $filename;

            } catch (\Exception $e) {
                // Nếu lỗi upload ảnh, ta chỉ log lại chứ không làm sập request
                // Bạn có thể return lỗi 500 ở đây nếu muốn bắt buộc phải up được ảnh
                // \Log::error("Lỗi upload ảnh: " . $e->getMessage());
            }
        }
        // ------------------------------------------------

        // Update password nếu có
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật thông tin thành công',
            'data' => $user
        ], 200);
    }

    // =========================
    // DELETE
    // =========================
    public function destroy($id)
    {
        $user = UserCustomer::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Người dùng không tồn tại',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa người dùng thành công',
        ], 200);
    }


}
