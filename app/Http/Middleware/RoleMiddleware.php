<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return response()->json([
                'message' => 'Token đã hết hạn'
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'message' => 'Token không hợp lệ'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Chưa đăng nhập'
            ], 401);
        }

        if (!$user) {
            return response()->json([
                'message' => 'Người dùng không tồn tại'
            ], 404);
        }

        // ⚠️ SỬA Ở ĐÂY
        if (!in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Bạn không có quyền truy cập'
            ], 403);
        }

        return $next($request);
    }
}
