<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserCustomer;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        try {
            // Tổng doanh thu (chỉ tính đơn thành công status = 3)
            $total_revenue = OrderItem::join('order', 'order_item.order_id', '=', 'order.id')
                ->where('order.status', 2)
                ->sum('order_item.amount');

            return response()->json([
                'status' => true,
                'message' => 'Lấy thống kê thành công',
                'data' => [
                    'total_revenue' => (float) $total_revenue,
                    'total_orders' => Order::count(),
                    'total_customers' => UserCustomer::where('id', '!=', 1)->count(),
                    'total_refunds' => Order::where('status', 4)->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi Server: ' . $e->getMessage()
            ], 500);
        }
    }
}
