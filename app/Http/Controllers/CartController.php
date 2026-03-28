<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CartController extends Controller
{
    /**
     * Helper: Load sản phẩm kèm giá Sale
     */
    private function loadCartWithProducts($cart)
    {
        $now = Carbon::now('Asia/Ho_Chi_Minh')->toDateTimeString();

        $cart->load(['items.product' => function ($query) use ($now) {
            // ✅ Laravel tự thêm prefix nnd_ vào products
            $query->select([
                'products.*', 
                
                // 🔥 DB::raw là SQL thuần nên Laravel KHÔNG tự thêm prefix. 
                // Ta PHẢI giữ nguyên nnd_ ở đây thì mới chạy đúng.
                DB::raw("(
                    SELECT price_sale 
                    FROM nnd_product_sale_items 
                    JOIN nnd_product_sale ON nnd_product_sale.id = nnd_product_sale_items.product_sale_id
                    WHERE nnd_product_sale_items.product_id = nnd_products.id
                    AND nnd_product_sale.status = 1
                    AND nnd_product_sale.date_begin <= '$now'
                    AND nnd_product_sale.date_end >= '$now'
                    ORDER BY price_sale ASC
                    LIMIT 1
                ) as sale_price")
            ]);
        }]);

        return $cart;
    }

    /**
     * Helper: Tính giá thực tế
     */
    private function getFinalPrice($productId)
    {
        $now = Carbon::now('Asia/Ho_Chi_Minh');
        $product = Product::find($productId);

        if (!$product) return 0;

        // ✅ Sửa: Bỏ 'nnd_' vì DB::table sẽ tự động thêm prefix từ file config
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
        if (!$user) return response()->json(['message' => 'Chưa đăng nhập'], 401);

        $cart = Cart::where('user_id', $user->id)->where('status', 'active')->first();

        if (!$cart) return response()->json(['cart' => null]);

        $cart = $this->loadCartWithProducts($cart);

        $cart->setRelation('items', $cart->items->filter(function ($item) {
            return $item->product !== null;
        })->values());

        return response()->json(['cart' => $cart]);
    }

    // 2. THÊM VÀO GIỎ
    public function addToCart(Request $request)
    {
        // ✅ SỬA LẠI: Bỏ 'nnd_' trong validation. Laravel sẽ tự kiểm tra bảng 'nnd_products'
        $request->validate([
            'product_id' => 'required|exists:products,id', 
            'quantity' => 'required|integer|min:1',
        ]);

        $user = auth()->user();
        if (!$user) return response()->json(['message' => 'Chưa đăng nhập'], 401);

        $finalPrice = $this->getFinalPrice($request->product_id);

        $cart = Cart::firstOrCreate(
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

        $cart = $this->loadCartWithProducts($cart);

        return response()->json([
            'message' => 'Đã thêm vào giỏ hàng',
            'cart' => $cart
        ]);
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
        
        if (!$cart) return response()->json(['message' => 'Giỏ hàng không tồn tại'], 404);

        $item = CartItem::where('cart_id', $cart->id)->where('product_id', $request->product_id)->first();
        
        if ($item) {
            $item->quantity = $request->quantity;
            $item->price = $this->getFinalPrice($request->product_id);
            $item->save();
        }

        $cart = $this->loadCartWithProducts($cart);
        return response()->json(['message' => 'Cập nhật thành công', 'cart' => $cart]);
    }

    // 4. MERGE CART
    public function mergeCart(Request $request)
    {
        $user = auth()->user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id, 'status' => 'active']);

        if($request->items && is_array($request->items)){
            foreach ($request->items as $itemData) {
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
        $cart = $this->loadCartWithProducts($cart);
        return response()->json(['message' => 'Đồng bộ thành công', 'cart' => $cart]);
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