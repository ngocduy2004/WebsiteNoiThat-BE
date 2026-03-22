<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProductSale;
use App\Models\ProductSaleItem;

class ProductSaleItemController extends Controller
{
    public function store(Request $request)
    {
        // Sử dụng Transaction để đảm bảo cả 2 bảng cùng lưu thành công
        DB::beginTransaction();

        try {
            // 1. Validate dữ liệu
            $request->validate([
                'name' => 'required|string',
                'date_begin' => 'required|date',
                'date_end' => 'required|date|after:date_begin',
                'products' => 'required|array|min:1', // Frontend gửi lên mảng 'products'
                'products.*.product_id' => 'required|numeric',
                'products.*.price_sale' => 'required|numeric',
            ]);

            // 2. Tạo bảng cha (ProductSale)
            $sale = ProductSale::create([
                'name'       => $request->name,
                'date_begin' => $request->date_begin,
                'date_end'   => $request->date_end,
                'status'     => 1,
                'created_by' => 1, // Tạm thời set cứng hoặc lấy Auth::id()
            ]);

            // 3. Tạo bảng con (ProductSaleItem)
            $itemsData = [];
            foreach ($request->products as $product) {
                // Gom dữ liệu để insert 1 lần (nhanh hơn loop create)
                $itemsData[] = [
                    'product_sale_id' => $sale->id,
                    'product_id'      => $product['product_id'],
                    'price_sale'      => $product['price_sale'],
                    'qty'             => $product['qty'] ?? 1,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
            
            // Insert hàng loạt vào bảng con
            ProductSaleItem::insert($itemsData);

            DB::commit(); // Lưu vào DB

            return response()->json([
                'status' => true,
                'message' => 'Tạo chương trình khuyến mãi thành công!',
                'sale_id' => $sale->id
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack(); // Hoàn tác nếu lỗi

            return response()->json([
                'status' => false,
                'message' => 'Lỗi Server: ' . $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}