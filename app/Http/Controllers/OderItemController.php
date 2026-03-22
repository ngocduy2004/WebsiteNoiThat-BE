<?php

namespace App\Http\Controllers;
use App\Models\OrderItem;

use Illuminate\Http\Request;

class OderItemController extends Controller
{
     public function index(Request $request)
    {
        $query = OrderItem::query();

        if ($request->has('order_id')) {
            $query->where('order_id', $request->order_id);
        }

        $details = $query->get();

        return response()->json([
            'status' => true,
            'data' => $details,
            'message' => 'Lấy chi tiết đơn hàng thành công',
        ]);
    }

    public function store(Request $request)
    {
        $detail = OrderItem::create($request->all());

        return response()->json([
            'status' => true,
            'data' => $detail,
            'message' => 'Tạo chi tiết đơn hàng thành công',
        ], 201);
    }

    public function show($id)
    {
        $detail = OrderItem::find($id);

        if (!$detail) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy chi tiết đơn hàng',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $detail,
        ]);
    }

    public function update(Request $request, $id)
    {
        $detail = OrderItem::find($id);

        if (!$detail) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy chi tiết đơn hàng',
            ], 404);
        }

        $detail->update($request->all());

        return response()->json([
            'status' => true,
            'data' => $detail,
            'message' => 'Cập nhật chi tiết đơn hàng thành công',
        ]);
    }

    public function destroy($id)
    {
        $detail = OrderItem::find($id);

        if (!$detail) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy chi tiết đơn hàng',
            ], 404);
        }

        $detail->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa chi tiết đơn hàng thành công',
        ]);
    }
}
