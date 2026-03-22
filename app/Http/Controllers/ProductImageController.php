<?php

namespace App\Http\Controllers;
use App\Models\ProductImage;

use Illuminate\Http\Request;

class ProductImageController extends Controller
{
      public function index(Request $request)
    {
        $query = ProductImage::query();

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $images = $query->get();

        return response()->json([
            'status' => true,
            'data' => $images,
            'message' => 'Lấy danh sách hình ảnh sản phẩm thành công',
        ]);
    }

    public function store(Request $request)
    {
        $image = ProductImage::create($request->all());

        return response()->json([
            'status' => true,
            'data' => $image,
            'message' => 'Thêm hình ảnh sản phẩm thành công',
        ], 201);
    }

    public function show($id)
    {
        $image = ProductImage::find($id);

        if (!$image) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy hình ảnh',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $image,
        ]);
    }

    public function update(Request $request, $id)
    {
        $image = ProductImage::find($id);

        if (!$image) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy hình ảnh',
            ], 404);
        }

        $image->update($request->all());

        return response()->json([
            'status' => true,
            'data' => $image,
            'message' => 'Cập nhật hình ảnh thành công',
        ]);
    }

    public function destroy($id)
    {
        $image = ProductImage::find($id);

        if (!$image) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy hình ảnh',
            ], 404);
        }

        $image->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa hình ảnh thành công',
        ]);
    }
}
