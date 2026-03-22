<?php

namespace App\Http\Controllers;
use App\Models\Setting;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $query = Setting::query();

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


        if ($request->has('search') && $request->search != "") {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%');
        }

        $query->orderBy('created_at', 'desc');

        $setting = $query->get();

        $total = Setting::count();

        return response()->json([
            'status' => true,
            'data' => $setting,
            'total' => $total,
            'message' => 'Lấy danh sách Danh mục thành công',
            'error' => null,
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'hotline' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ]);

        $setting = Setting::create([
            'site_name' => $request->site_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'hotline' => $request->hotline,
            'address' => $request->address,
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Tạo cài đặt thành công',
            'data' => $setting,
        ], 200);
    }

    public function show($id)
    {
        $setting = Setting::find($id);

        if (!$setting) {
            return response()->json([
                'status' => false,
                'message' => 'Cài đặt không tồn tại',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $setting,
        ], 200);
    }
    public function update(Request $request, $id)
    {
        $setting = Setting::find($id);

        if (!$setting) {
            return response()->json([
                'status' => false,
                'message' => 'Cài đặt không tồn tại',
            ], 404);
        }

        $request->validate([
            'site_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'hotline' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ]);

        $setting->update([
            'site_name' => $request->site_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'hotline' => $request->hotline,
            'address' => $request->address,
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật cài đặt thành công',
        ], 200);
    }
    public function destroy($id)
    {
        $setting = Setting::find($id);

        if (!$setting) {
            return response()->json([
                'status' => false,
                'message' => 'Cài đặt không tồn tại',
            ], 404); 
        }

        $setting->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa cài đặt thành công',
        ], 200);
    }

}
