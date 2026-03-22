<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSaleItem extends Model
{
    protected $table = 'product_sale_items';

    protected $fillable = [
        'product_sale_id',
        'product_id',
        'price_sale',
        'qty',
    ];

    // Quan hệ: Item thuộc về 1 Sản phẩm
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // 👇 QUAN TRỌNG: Liên kết để lấy thông tin ngày tháng của đợt Sale
    public function sale()
    {
        return $this->belongsTo(ProductSale::class, 'product_sale_id', 'id');
    }

    // Liên kết để lấy thông tin sản phẩm (nếu cần)
   
}