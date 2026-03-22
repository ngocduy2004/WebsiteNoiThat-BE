<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ProductSale extends Model
{
    protected $table = 'product_sale'; // lưu ý: bảng nên đặt số nhiều
    protected $fillable = ['name', 'date_begin', 'date_end', 'status', 'created_by', 'updated_by'];
    public $timestamps = true;

    // 1 sale có nhiều sản phẩm
    public function items()
    {
        return $this->hasMany(ProductSaleItem::class, 'product_sale_id');
    }

    // Lấy danh sách sản phẩm trực tiếp qua bảng items
    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'product_sale_items',
            'product_sale_id',
            'product_id'
        )->withPivot('price_sale', 'qty');
    }


}