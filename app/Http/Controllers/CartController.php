<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CartController extends Controller
{
    /**
     * Helper: Load sản phẩm kèm giá Sale
     * Tối ưu truy vấn SQL tránh xung đột tên cột p.*
     */
    private function loadCartWithProducts($cart)
    {
        if (!$cart)
            return $cart;

        $now = Carbon::now('Asia/Ho_Chi_Minh')->toDateTimeString();

        // Lấy chính xác tên bảng từ Model (Sẽ tự ra NND_products)
        $productTable = (new Product())->getTable();
        $saleItemTable = DB::getTablePrefix() . 'product_sale_items';
        $saleTable = DB::getTablePrefix() . 'product_sale';

        $cart->load([
            'items.product' => function ($query) use ($now, $productTable, $saleItemTable, $saleTable) {
                $query->select([
                    "$productTable.*",
                    // SỬA TẠI ĐÂY: Dùng biến $productTable thay vì chữ 'products'
                    DB::raw("(
                    SELECT price_sale 
                    FROM $saleItemTable psi
                    JOIN $saleTable ps ON ps.id = psi.product_sale_id
                    WHERE psi.product_id = $productTable.id
                    AND ps.status = 1
                    AND ps.date_begin <= ?
                    AND ps.date_end >= ?
                    ORDER BY price_sale ASC
                    LIMIT 1
                ) as sale_price")
                ])->setBindings([$now, $now], 'select');
            }
        ]);

        return $cart;
    }
    /**
     * Helper: Tính giá thực tế (Dùng Query Builder tự động xử lý Prefix)
     */
    private function getFinalPrice($productId)
    {
        $now = Carbon::now('Asia/Ho_Chi_Minh');
        $product = Product::find($productId);

        if (!$product)
            return 0;

        $saleInfo = DB::table('product_sale_items')
            ->join('product_sale', 'product_sale_items.product_sale_id', '=', 'product_sale.id')
            ->where('product_sale_items.product_id', $productId)
            ->where('product_sale.status', 1)
            ->where('product_sale.date_begin', '<=', $now)
            ->where('product_sale.date_end', '>=', $now)
            ->orderBy('product_sale_items.price_sale', 'asc')
            ->select('product_sale_items.price_sale')
            ->first();

        return $saleInfo ? $saleInfo->price_sale : $product->price_buy;
    }

    // 1. LẤY GIỎ HÀNG
    public function getCart()
    {
        $user = auth('sanctum')->user() ?? auth()->user();
        if (!$user)
            return response()->json(['message' => 'Chưa đăng nhập'], 401);

        $cart = Cart::where('user_id', $user->id)->where('status', 'active')->first();

        if (!$cart)
            return response()->json(['cart' => ['items' => []]]);

        $cart = $this->loadCartWithProducts($cart);
        $cart->setRelation('items', $cart->items->filter(fn($item) => $item->product !== null)->values());

        return response()->json(['cart' => $cart]);
    }

    // 2. THÊM VÀO GIỎ (Sử dụng DB Transaction để an toàn)
    public function addToCart(Request $request)
    {
        try {
            $user = auth('sanctum')->user() ?? auth()->user();
            if (!$user)
                return response()->json(['message' => 'Bạn chưa đăng nhập'], 401);

            $request->validate([
                'product_id' => 'required|exists:NND_products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $finalPrice = $this->getFinalPrice($request->product_id);

            $cart = DB::transaction(function () use ($user, $request, $finalPrice) {
                $cart = Cart::firstOrCreate(['user_id' => $user->id, 'status' => 'active']);

                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('product_id', $request->product_id)
                    ->first();

                if ($cartItem) {
                    $cartItem->increment('quantity', $request->quantity, ['price' => $finalPrice]);
                } else {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $request->product_id,
                        'quantity' => $request->quantity,
                        'price' => $finalPrice,
                    ]);
                }
                return $cart;
            });

            return response()->json([
                'message' => 'Đã thêm vào giỏ hàng',
                'cart' => $this->loadCartWithProducts($cart)
            ]);
        } catch (\Exception $e) {
            Log::error("Add to cart error: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 3. CẬP NHẬT
    public function updateCart(Request $request)
    {
        $user = auth('sanctum')->user() ?? auth()->user();
        if (!$user)
            return response()->json(['message' => 'Unauthorized'], 401);

        $request->validate([
            'product_id' => 'required',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('user_id', $user->id)->where('status', 'active')->first();
        if (!$cart)
            return response()->json(['message' => 'Giỏ hàng rỗng'], 404);

        $item = CartItem::where('cart_id', $cart->id)->where('product_id', $request->product_id)->first();
        if ($item) {
            $item->update([
                'quantity' => $request->quantity,
                'price' => $this->getFinalPrice($request->product_id)
            ]);
        }

        return response()->json([
            'message' => 'Cập nhật thành công',
            'cart' => $this->loadCartWithProducts($cart)
        ]);
    }

    // 4. MERGE CART
    public function mergeCart(Request $request)
    {
        try {
            $user = auth('sanctum')->user() ?? auth()->user();
            if (!$user)
                return response()->json(['message' => 'Unauthorized'], 401);

            $cart = DB::transaction(function () use ($user, $request) {
                $cart = Cart::firstOrCreate(['user_id' => $user->id, 'status' => 'active']);

                if ($request->items && is_array($request->items)) {
                    foreach ($request->items as $itemData) {
                        if (!isset($itemData['product_id']))
                            continue;

                        $finalPrice = $this->getFinalPrice($itemData['product_id']);

                        CartItem::updateOrCreate(
                            ['cart_id' => $cart->id, 'product_id' => $itemData['product_id']],
                            ['price' => $finalPrice, 'quantity' => DB::raw("quantity + " . ($itemData['quantity'] ?? 1))]
                        );
                    }
                }
                return $cart;
            });

            return response()->json([
                'message' => 'Đồng bộ thành công',
                'cart' => $this->loadCartWithProducts($cart)
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 5. REMOVE ITEM
    public function removeItem($productId)
    {
        $user = auth('sanctum')->user() ?? auth()->user();
        $cart = Cart::where('user_id', $user->id)->where('status', 'active')->first();

        if ($cart) {
            CartItem::where('cart_id', $cart->id)->where('product_id', $productId)->delete();
            $cart = $this->loadCartWithProducts($cart);
        }

        return response()->json(['message' => 'Đã xóa', 'cart' => $cart]);
    }

    // 6. CLEAR CART
    public function clearCart()
    {
        $user = auth('sanctum')->user() ?? auth()->user();
        $cart = Cart::where('user_id', $user->id)->where('status', 'active')->first();

        if ($cart) {
            CartItem::where('cart_id', $cart->id)->delete();
            $cart->setRelation('items', collect([]));
        }

        return response()->json(['message' => 'Đã làm trống giỏ hàng', 'cart' => $cart]);
    }
}