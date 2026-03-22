<?php

namespace App\Http\Controllers; // ⚠️ Namespace phải là App\Http\Controllers

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProductSale;
use App\Models\ProductSaleItem; // ⚠️ Phải có dòng này để load quan hệ items

class ProducSaleController extends Controller
{
    // 1. Hàm lấy danh sách (QUAN TRỌNG ĐỂ HIỂN THỊ)
    public function index(Request $request)
    {
        $limit = $request->limit ?? 5;
        $search = $request->search ?? '';

        $query = ProductSale::query()
            ->withCount('items') // 🔥 BẮT BUỘC
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            })
            ->orderBy('created_at', 'desc');

        $data = $query->paginate($limit);

        return response()->json([
            'status' => true,
            'data' => $data->items(),
            'total' => $data->total()
        ]);
    }

    // 2. Hàm tạo (Giữ nguyên code cũ của bạn)
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // ... (Code validate và create cũ) ...
            $sale = ProductSale::create([
                'name' => $request->name,
                'date_begin' => $request->date_begin,
                'date_end' => $request->date_end,
                'status' => 1,
                'created_by' => 1,
            ]);

            $itemsData = [];
            foreach ($request->products as $product) {
                $itemsData[] = [
                    'product_sale_id' => $sale->id,
                    'product_id' => $product['product_id'],
                    'price_sale' => $product['price_sale'],
                    'qty' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            ProductSaleItem::insert($itemsData);

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Tạo thành công!']);
        } catch (\Throwable $e) {
            DB::rollBack();
            // Log lỗi ra để biết tại sao
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 3. Hàm xóa
    public function destroy($id)
    {
        $sale = ProductSale::find($id);
        if ($sale) {
            ProductSaleItem::where('product_sale_id', $id)->delete();
            $sale->delete();
            return response()->json(['status' => true, 'message' => 'Xóa thành công']);
        }
        return response()->json(['status' => false, 'message' => 'Không tìm thấy']);
    }
    // Trong file app/Http/Controllers/ProductSaleController.php

    // 1. Hàm Show (Để load dữ liệu lên form Edit)
    public function show($id)
    {
        // Load Sale kèm theo danh sách items và thông tin sản phẩm của items đó
        $sale = ProductSale::with(['items.product'])->find($id);

        if ($sale) {
            return response()->json(['status' => true, 'data' => $sale]);
        }
        return response()->json(['status' => false, 'message' => 'Không tìm thấy']);
    }

    // 2. Hàm Update (Để lưu thay đổi)
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $sale = ProductSale::find($id);
            if (!$sale) {
                return response()->json(['status' => false, 'message' => 'Không tìm thấy']);
            }

            // Cập nhật thông tin cha
            $sale->update([
                'name' => $request->name,
                'date_begin' => $request->date_begin,
                'date_end' => $request->date_end,
                'updated_by' => 1, // Hoặc Auth::id()
            ]);

            // Cập nhật danh sách con: XÓA CŨ -> THÊM MỚI (Cách đơn giản nhất)
            ProductSaleItem::where('product_sale_id', $id)->delete();

            $itemsData = [];
            foreach ($request->products as $product) {
                $itemsData[] = [
                    'product_sale_id' => $sale->id,
                    'product_id' => $product['product_id'],
                    'price_sale' => $product['price_sale'],
                    'qty' => $product['qty'] ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            ProductSaleItem::insert($itemsData);

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Cập nhật thành công']);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}