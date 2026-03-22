<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product_store extends Model
{
    protected $table = 'product_store';

    protected $fillable = [
        'product_id',
        'price_root',
        'qty',
        'created_by',
        'updated_by',
        'status',
    ];

    protected $casts = [
        'price_root' => 'float',
        'qty' => 'integer',
        'status' => 'boolean',
    ];


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
