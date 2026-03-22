<?php

namespace App\Http\Controllers;
use App\Models\Promotion;
use App\Models\ProductSale;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        // Sử dụng withCount để lấy thêm thuộc tính items_count (số lượng sản phẩm)
        // Giả định quan hệ trong Model Promotion tên là 'items'
        $query = Promotion::withCount('items');

        // 1. Tìm kiếm theo tên hoặc mã
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        // 2. Tính tổng số bản ghi sau khi lọc (để phân trang chính xác)
        $total = $query->count();

        // 3. Sắp xếp và Phân trang
        if ($request->has('limit') && $request->has('page')) {
            $limit = (int) $request->input('limit');
            $page = (int) $request->input('page');
            $offset = ($page - 1) * $limit;

            $query->offset($offset)->limit($limit);
        }

        $promotions = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => true,
            'data' => $promotions, // Mỗi object sẽ có field items_count
            'total' => $total,
            'message' => 'Lấy danh sách khuyến mãi thành công',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date_begin' => 'required|date',
            'date_end' => 'required|date|after:date_begin',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.price_sale' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // 1️⃣ Tạo promotion
            $promotion = Promotion::create([
                'name' => $request->name,
                'date_begin' => $request->date_begin,
                'date_end' => $request->date_end,
                'status' => 1, // 1 là hoạt động
            ]);

            // 2️⃣ Gắn sản phẩm vào bảng product_sale
            foreach ($request->products as $item) {
                ProductSale::create([
                    'promotion_id' => $promotion->id,
                    'product_id' => $item['product_id'],
                    'price_sale' => $item['price_sale'],
                    'qty' => $item['qty'] ?? 1,
                    'date_begin' => $request->date_begin, // Đồng bộ thời gian để dễ truy vấn
                    'date_end' => $request->date_end,
                ]);
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Tạo thành công'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // Thêm vào ProductController hoặc nơi bạn lấy danh sách sản phẩm ngoài trang chủ
    public function getActiveSales()
    {
        $now = now(); // Lấy thời gian hiện tại

        $products = Product::join('product_sale', 'products.id', '=', 'product_sale.product_id')
            ->where('product_sale.date_begin', '<=', $now)
            ->where('product_sale.date_end', '>=', $now)
            ->select('products.*', 'product_sale.price_sale', 'product_sale.date_end')
            ->get();

        return response()->json(['data' => $products]);
    }

}
