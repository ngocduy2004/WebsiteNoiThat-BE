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

     */

    // CartController.php

    private function loadCartWithProducts($cart)
    {
        $now = Carbon::now('Asia/Ho_Chi_Minh')->toDateTimeString();
        $productTable = (new Product())->getTable();
        $saleItemTable = DB::getTablePrefix() . 'product_sale_items';
        $saleTable = DB::getTablePrefix() . 'product_sale';

        $cart->load([
            'items.product' => function ($query) use ($now, $productTable, $saleItemTable, $saleTable) {
                $query->select([
                    "$productTable.*",
                    DB::raw("(
                        SELECT price_sale 
                        FROM $saleItemTable
                        JOIN $saleTable ON $saleTable.id = $saleItemTable.product_sale_id
                        WHERE $saleItemTable.product_id = $productTable.id
                        AND $saleTable.status = 1
                        AND $saleTable.date_begin <= ?
                        AND $saleTable.date_end >= ?
                        ORDER BY price_sale ASC
                        LIMIT 1
                    ) as sale_price")
                ])->setBindings([$now, $now], 'select');
            }
        ]);

        return $cart;
    }

    /**

     * Helper: Tính giá thực tế

     */

    /**
     * Helper: Tính giá thực tế (Fix lỗi Prefix bảng trên Production)
     */
    private function getFinalPrice($productId)
    {
        $now = Carbon::now('Asia/Ho_Chi_Minh');
        $product = Product::find($productId);

        if (!$product)
            return 0;

        // Dùng Query Builder để Laravel tự động quản lý Prefix bảng (nnd_...)
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
        $user = auth()->user();
        if (!$user)
            return response()->json(['message' => 'Chưa đăng nhập'], 401);

        $cart = Cart::where('user_id', $user->id)->where('status', 'active')->first();

        if (!$cart)
            return response()->json(['cart' => ['items' => []]]);

        $cart = $this->loadCartWithProducts($cart);

        // Lọc bỏ các item có sản phẩm bị xóa
        $cart->setRelation('items', $cart->items->filter(fn($item) => $item->product !== null)->values());

        return response()->json(['cart' => $cart]);
    }



    // 2. THÊM VÀO GIỎ

    // 2. THÊM VÀO GIỎ
    public function addToCart(Request $request)
    {
        try {
            // Dùng auth('sanctum') nếu bạn dùng Laravel Sanctum
            $user = auth('sanctum')->user() ?? auth()->user();

            if (!$user) {
                return response()->json(['message' => 'Bạn chưa đăng nhập'], 401);
            }

            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $finalPrice = $this->getFinalPrice($request->product_id);

            // Sử dụng Eloquent thay vì DB::table để tự động nhận Prefix bảng
            $cart = Cart::updateOrCreate(
                ['user_id' => $user->id, 'status' => 'active']
            );

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $request->product_id)
                ->first();

            if ($cartItem) {
                $cartItem->quantity += $request->quantity;
                $cartItem->price = $finalPrice;
                $cartItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity,
                    'price' => $finalPrice,
                ]);
            }

            return response()->json([
                'message' => 'Đã thêm vào giỏ hàng',
                'cart' => $this->loadCartWithProducts($cart)
            ]);

        } catch (\Exception $e) {
            // Log lỗi ra để check trên Railway Dashboard
            Log::error("Giỏ hàng lỗi: " . $e->getMessage());
            return response()->json([
                'error' => 'Lỗi Server: ' . $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    // 3. CẬP NHẬT

    public function updateCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'quantity' => 'required|integer|min:1',
        ]);
        $user = auth()->user();
        $cart = Cart::where('user_id', $user->id)->where('status', 'active')->first();
        if (!$cart)
            return response()->json(['message' => 'Giỏ hàng không tồn tại'], 404);

        $item = CartItem::where('cart_id', $cart->id)->where('product_id', $request->product_id)->first();
        if ($item) {
            $item->quantity = $request->quantity;
            $item->price = $this->getFinalPrice($request->product_id);
            $item->save();
        }
        $cart = $this->loadCartWithProducts($cart);

        return response()->json(['message' => 'Cập nhật thành công', 'cart' => $cart]);

    }


    // 4. MERGE CART (Dùng khi vừa Login xong)
    public function mergeCart(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user)
                return response()->json(['message' => 'Unauthorized'], 401);

            $cart = Cart::firstOrCreate(['user_id' => $user->id, 'status' => 'active']);

            if ($request->items && is_array($request->items)) {
                foreach ($request->items as $itemData) {
                    // Kiểm tra id sản phẩm có tồn tại không
                    if (!isset($itemData['product_id']))
                        continue;

                    $finalPrice = $this->getFinalPrice($itemData['product_id']);

                    $cartItem = CartItem::where('cart_id', $cart->id)
                        ->where('product_id', $itemData['product_id'])
                        ->first();

                    if ($cartItem) {
                        $cartItem->quantity += $itemData['quantity'];
                        $cartItem->price = $finalPrice;
                        $cartItem->save();
                    } else {
                        CartItem::create([
                            'cart_id' => $cart->id,
                            'product_id' => $itemData['product_id'],
                            'quantity' => $itemData['quantity'],
                            'price' => $finalPrice,
                        ]);
                    }
                }
            }

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

        $user = auth()->user();

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

        $user = auth()->user();

        $cart = Cart::where('user_id', $user->id)->where('status', 'active')->first();

        if ($cart) {

            CartItem::where('cart_id', $cart->id)->delete();

            $cart->setRelation('items', collect([]));

        }

        return response()->json(['message' => 'Đã xóa giỏ hàng', 'cart' => $cart]);

    }

}