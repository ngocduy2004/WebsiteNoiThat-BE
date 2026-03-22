<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderConfirm extends Mailable
{
    use Queueable, SerializesModels;

    public $order; // Biến này chứa dữ liệu đơn hàng

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        return $this->subject('Xác nhận đơn hàng #' . $this->order->id)
                    ->view('emails.order_confirm'); // Trỏ đến file giao diện (Bước 2)
    }
}