<?php

namespace App\Http\Controllers;

use App\Models\UserCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    // =================== LOGIN ===================
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $user = UserCustomer::where('username', $request->username)
            ->where('status', 1)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Sai tài khoản hoặc mật khẩu'
            ], 401);
        }

        // Tạo token từ user
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60, // số giây
            'role' => $user->roles,
            'user' => $user
        ]);
    }

    // =================== LOGOUT ===================
    public function logout()
    {
        try {
            JWTAuth::parseToken()->invalidate();

            return response()->json([
                'message' => 'Đăng xuất thành công'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token không hợp lệ'
            ], 401);
        }
    }

    // =================== ME ===================
    public function me()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'message' => 'Người dùng không tồn tại'
                ], 404);
            }

            return response()->json([
                'user' => $user
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token không hợp lệ'
            ], 401);
        }
    }

    // Thêm vào trong class AuthController
    // App\Http\Controllers\AuthController.php

    public function register(Request $request)
    {
        // 1. Validate
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'phone' => 'required|string|max:15',
            'password' => 'required|string|min:6',
        ]);

        try {
            // 2. Tạo User với đầy đủ các trường mà Model/DB yêu cầu
            $user = UserCustomer::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'roles' => 'user',
                'status' => 1,
                'phone' => $request->phone,        // Đảm bảo không bị null nếu DB yêu cầu
                'avatar' => 'default.png',
                'created_at' => now(),    // Vì bạn để public $timestamps = false
                'updated_at' => now(),
                'created_by' => 1,       // Hoặc ID của admin mặc định
                'updated_by' => 1,
            ]);

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'Đăng ký thành công',
                'token' => $token,
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            // Trả về lỗi chi tiết để bạn dễ debug
            return response()->json([
                'message' => 'Lỗi database: ' . $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}