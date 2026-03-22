<?php

namespace App\Http\Controllers;

use App\Models\Product_Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Product_StoreController extends Controller
{
    // =========================
    // LIST + SEARCH + PAGINATION
    // =========================
    public function index(Request $request)
    {
        // =========================
        // BASE QUERY
        // =========================
        $query = Product_Store::query()
            ->join('products', 'products.id', '=', 'product_store.product_id')
            ->select(
                'product_store.id',
                'product_store.product_id',
                'products.name as product_name',
                'product_store.price_root',
                'product_store.qty',
                'product_store.status',
                'product_store.created_at'
            );

        // =========================
        // SEARCH THEO TÊN SẢN PHẨM
        // =========================
        if ($request->filled('search')) {
            $query->where('products.name', 'like', '%' . $request->search . '%');
        }

        // =========================
        // TOTAL (CLONE QUERY)
        // =========================
        $total = (clone $query)->count();

        // =========================
        // PAGINATION
        // =========================
        if ($request->filled('limit') && $request->filled('page')) {
            $limit = (int) $request->limit;
            $page = (int) $request->page;

            $query->offset(($page - 1) * $limit)
                ->limit($limit);
        }

        // =========================
        // ORDER
        // =========================
        $productStores = $query
            ->orderBy('product_store.created_at', 'desc')
            ->get();

        // =========================
        // RESPONSE
        // =========================
        return response()->json([
            'status' => true,
            'data' => $productStores,
            'total' => $total,
            'limit' => $request->limit ?? null,
            'message' => 'Lấy danh sách kho sản phẩm thành công',
            'error' => null,
        ], 200);
    }


    // =========================
    // STORE
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'price_root' => 'required|numeric|min:0',
            'qty' => 'required|integer|min:0',
            'status' => 'required|in:0,1',
        ]);

        $data = Product_Store::create([
            'product_id' => $request->product_id,
            'price_root' => $request->price_root,
            'qty' => $request->qty,
            'status' => $request->status,
            'created_by' => Auth::id() ?? 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Thêm sản phẩm vào kho thành công',
            'data' => $data,
        ], 200);
    }

    // =========================
    // SHOW DETAIL
    // =========================
    public function show($id)
    {
        $product_store = Product_Store::find($id);

        if (!$product_store) {
            return response()->json([
                'status' => false,
                'message' => 'Kho sản phẩm không tồn tại',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $product_store,
        ], 200);
    }

    // =========================
    // UPDATE
    // =========================
    public function update(Request $request, $id)
    {
        $store = Product_Store::find($id);

        if (!$store) {
            return response()->json([
                'status' => false,
                'message' => 'Kho sản phẩm không tồn tại',
            ], 404);
        }

        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'price_root' => 'required|numeric|min:0',
            'qty' => 'required|integer|min:0',
            'status' => 'required|in:0,1',
        ]);

        $store->update([
            'product_id' => $request->product_id,
            'price_root' => $request->price_root,
            'qty' => $request->qty,
            'status' => $request->status,
            'updated_by' => Auth::id() ?? 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật kho sản phẩm thành công',
        ], 200);
    }

    // =========================
    // DELETE
    // =========================
    public function destroy($id)
    {
        $product_store = Product_Store::find($id);

        if (!$product_store) {
            return response()->json([
                'status' => false,
                'message' => 'Kho sản phẩm không tồn tại',
            ], 404);
        }

        $product_store->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa kho sản phẩm thành công',
        ], 200);
    }
}
