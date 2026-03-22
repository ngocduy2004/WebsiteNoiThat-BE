<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\OrderItem; // Import Model OrderItem
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirm; // Import Class Mail vừa tạo
use App\Models\Product;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query();

        // ======================================================
        // SỬA LỖI Ở ĐÂY: Dùng 'filled' thay vì 'has'
        // ======================================================
        // 'filled': Chỉ lọc khi user_id có giá trị thực sự (vd: ?user_id=5)
        // Nếu Admin gọi API mà không truyền user_id (hoặc truyền rỗng), nó sẽ bỏ qua dòng này => Hiện tất cả.
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Tìm kiếm theo tên người nhận hoặc mã đơn hàng
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('id', $search);
            });
        }

        // Lọc theo trạng thái (nếu Admin muốn xem riêng đơn Hủy, đơn Mới...)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Load chi tiết để hiển thị sản phẩm
        $orders = $query->with(['details.product.images'])
            ->orderBy('created_at', 'desc')
            ->get(); // Hoặc dùng ->paginate($limit) nếu muốn phân trang chuẩn Laravel

        return response()->json([
            'status' => true,
            'data' => $orders,
            'message' => 'Lấy danh sách thành công',
        ], 200);
    }
    public function cancel($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Đơn hàng không tồn tại'], 404);
        }

        // Chỉ cho phép hủy nếu đơn hàng đang ở trạng thái Chờ xử lý (ví dụ: status = 1)
        if ($order->status == 1) {
            $order->status = 0; // Giả sử 0 là Đã hủy
            $order->save();
            return response()->json(['status' => true, 'message' => 'Đã hủy đơn hàng thành công'], 200);
        }

        return response()->json(['status' => false, 'message' => 'Không thể hủy đơn hàng này'], 400);
    }


    // =========================
    // STORE
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'items' => 'required|array|min:1',
            'payment_method' => 'required|in:cod,vnpay',
        ]);

        DB::beginTransaction();
        try {
            // 1. Tạo đơn hàng
            $order = Order::create([
                'user_id' => $request->user_id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'note' => $request->note,
                'status' => 1,
                'payment_method' => $request->payment_method,
                'created_at' => now(),
                'created_by' => Auth::id() ?? 1,
            ]);

            // 2. Tạo chi tiết & Tính tổng tiền dựa trên bảng PRODUCTS
            $totalAmount = 0;
            foreach ($request->items as $item) {
                // Tìm sản phẩm gốc từ database
                $product = Product::findOrFail($item['product_id']);

                // ✅ CHỐT GIÁ: Lấy price_buy từ bảng products
                $price = (float) $product->price_buy;
                $qty = (int) $item['qty'];
                $amount = $price * $qty;
                $totalAmount += $amount;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'price' => $price, // Giá sản phẩm niêm yết tại thời điểm mua
                    'qty' => $qty,
                    'discount' => $item['discount'] ?? 0,
                    'amount' => $amount,
                ]);
            }

            DB::commit();

            // Xử lý thanh toán VNPAY (nếu chọn)
            if ($request->payment_method === 'vnpay') {
                $vnp_Url = $this->createVnPayUrl($order->id, $totalAmount);
                return response()->json([
                    'status' => true,
                    'payment_url' => $vnp_Url,
                    'method' => 'vnpay'
                ], 200);
            }

            return response()->json([
                'status' => true,
                'message' => 'Đặt hàng thành công với giá niêm yết',
                'data' => $order,
                'method' => 'cod'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    // Hàm VNPAY giữ nguyên
    // ==========================================
    // HÀM TẠO URL VNPAY (Đã cập nhật Code của bạn)
    // ==========================================
    private function createVnPayUrl($orderId, $amount)
    {
        // Lấy cấu hình từ .env
        $vnp_TmnCode = env('VNP_TMN_CODE', '79IK9ROT'); // Code của bạn
        $vnp_HashSecret = env('VNP_HASH_SECRET', 'UM1O9T2W3NVLKY45K9QWWOMGIFBXZDQF'); // Secret của bạn
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";

        // Link trả về Frontend sau khi thanh toán xong
        // Giả sử Next.js chạy cổng 3000
        $vnp_Returnurl = "http://localhost:3000/thank-you";

        $vnp_TxnRef = $orderId;
        $vnp_OrderInfo = "Thanh toan don hang #" . $orderId;
        $vnp_OrderType = "billpayment";
        $vnp_Amount = $amount * 100; // VNPAY yêu cầu nhân 100
        $vnp_Locale = "vn";
        $vnp_IpAddr = request()->ip();

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef
        );

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }


    // ==========================================
    // XỬ LÝ KẾT QUẢ TRẢ VỀ TỪ VNPAY
    // ==========================================
    // ==========================================
    // XỬ LÝ KẾT QUẢ TRẢ VỀ TỪ VNPAY
    // ==========================================
    public function vnpayReturn(Request $request)
    {
        // 1. Lấy mã phản hồi và ID đơn hàng từ VNPAY
        $vnp_ResponseCode = $request->vnp_ResponseCode; // '00' là thành công
        $orderId = $request->vnp_TxnRef;

        // 2. Tìm đơn hàng trong Database
        $order = Order::with('details')->find($orderId);

        if (!$order) {
            return redirect("http://localhost:3000/cart?message=don-hang-khong-ton-tai");
        }

        if ($vnp_ResponseCode == '00') {
            // --- TRƯỜNG HỢP: THANH TOÁN THÀNH CÔNG ---
            if ($order->status == 1) {
                $order->status = 2; // Cập nhật sang trạng thái "Đã thanh toán"
                $order->save();

                // Gửi mail xác nhận cho khách và admin
                try {
                    Mail::to($order->email)->send(new OrderConfirm($order));
                    Mail::to('ngocduy6379@gmail.com')->send(new OrderConfirm($order));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Lỗi gửi mail: " . $e->getMessage());
                }
            }
            // Chuyển hướng về trang thành công (Frontend sẽ gọi clearCart tại đây)
            return redirect("http://localhost:3000/thank-you?status=success&orderId=$orderId");

        } else {
            // --- TRƯỜNG HỢP: THANH TOÁN THẤT BẠI HOẶC BỊ HỦY ---

            // ✅ XÓA ĐƠN HÀNG: Để không xuất hiện trong trang quản lý và lịch sử
            if ($order->status == 1) {
                // Xóa chi tiết trước để tránh lỗi ràng buộc khóa ngoại (nếu có)
                $order->details()->delete();
                // Xóa đơn hàng chính
                $order->delete();
            }

            // ✅ TRẢ KHÁCH VỀ GIỎ HÀNG: 
            // Vì đơn hàng bị xóa và bạn chưa gọi clearCart ở Frontend, 
            // các sản phẩm vẫn sẽ còn nguyên trong giỏ hàng để khách nhấn "Thanh toán" lại.
            return redirect("http://localhost:3000/cart?status=failed&message=Thanh toán không thành công.");
        }
    }
    // =========================
    // SHOW DETAIL
    // =========================
    public function show($id)
    {
        // Load: chi tiết đơn -> sản phẩm -> hình ảnh
        $order = Order::with(['details.product.images'])->find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Đơn hàng không tồn tại',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $order,
        ], 200);
    }

    // =========================
    // UPDATE
    // =========================
    public function update(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Đơn hàng không tồn tại'], 404);
        }

        // --- SỬA ĐOẠN NÀY: KIỂM TRA NẾU CHỈ UPDATE STATUS ---
        // Nếu request gửi lên có 'status' và KHÔNG CÓ 'name' (tức là update nhanh trạng thái)
        if ($request->has('status') && !$request->has('name')) {
            $order->status = $request->status;
            $order->updated_at = now();
            // $order->updated_by = Auth::id(); // Nếu có Auth admin
            $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Cập nhật trạng thái thành công',
                'data' => $order
            ], 200);
        }
        // ----------------------------------------------------

        // Validate đầy đủ (cho trường hợp nhấn nút "Edit" để sửa thông tin người nhận)
        $request->validate([
            'user_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'note' => 'nullable|string',
            'status' => 'required|integer',
        ]);

        $order->update([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'note' => $request->note,
            'status' => $request->status,
            'updated_at' => now(),
            'updated_by' => Auth::id() ?? 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật đơn hàng thành công',
        ], 200);
    }
    // =========================
    // DELETE
    // =========================
    public function destroy($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Đơn hàng không tồn tại',
            ], 404);
        }

        $order->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa đơn hàng thành công',
        ], 200);
    }

}
