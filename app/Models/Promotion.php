<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $table = 'promotion';

    protected $fillable = [
        'name',
        'code',
        'description',
        'discount_type',
        'discount_value',
        'date_begin',
        'date_end',
        'created_by',
        'updated_by',
        'status'
    ];

    // public function productSales()
    // {
    //     return $this->hasMany(ProductSale::class, 'promotion_id');
    // }

    public function items()
    {
        // Một chương trình sale có nhiều dòng chi tiết sản phẩm
        return $this->hasMany(ProductSale::class, 'promotion_id');
    }


}
