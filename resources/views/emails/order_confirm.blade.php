<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .header { background: #333; color: #fff; padding: 10px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>ĐƠN HÀNG MỚI #{{ $order->id }}</h2>
    </div>
    
    <p>Xin chào <strong>{{ $order->name }}</strong>,</p>
    <p>Cảm ơn bạn đã đặt hàng. Dưới đây là thông tin chi tiết:</p>

    <p>
        <strong>SĐT:</strong> {{ $order->phone }}<br>
        <strong>Địa chỉ:</strong> {{ $order->address }}<br>
        <strong>Thanh toán:</strong> {{ strtoupper($order->payment_method) }}
    </p>

    <table class="table">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Giá</th>
                <th>SL</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($order->details as $item)
                @php 
                    $subtotal = $item->price * $item->qty;
                    $total += $subtotal;
                @endphp
                <tr>
                    <td>{{ $item->product_id }}</td>
                    <td>{{ number_format($item->price) }}₫</td>
                    <td>{{ $item->qty }}</td>
                    <td>{{ number_format($subtotal) }}₫</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3 style="text-align: right; color: red;">Tổng tiền: {{ number_format($total) }}₫</h3>
</body>
</html>