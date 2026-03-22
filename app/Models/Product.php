<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'thumbnail',
        'content',
        'description',
        'price_buy',
        'created_by',
        'status',
    ];
    public $timestamps = false;

    protected $appends = ['thumbnail_url', 'formatted_attributes'];


    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }

    public function productAttributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function stores()
    {
        return $this->hasMany(Product_store::class, 'product_id');
    }


    // ✅ HÀM QUAN TRỌNG NHẤT
    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail
            ? asset('storage/' . $this->thumbnail) // Laravel storage chuẩn
            : null;
    }



    public function getFormattedAttributesAttribute()
    {
        // Nhóm các giá trị theo tên thuộc tính
        return $this->productAttributes->groupBy('attribute.name')->map(function ($items, $name) {
            return [
                'name' => $name,
                'values' => $items->pluck('value')
            ];
        })->values();
    }

    // Quan hệ: Một sản phẩm có thể nằm trong nhiều đợt sale (chi tiết sale)
    public function sale_items()
    {
        return $this->hasMany(ProductSaleItem::class, 'product_id', 'id');
    }
}
