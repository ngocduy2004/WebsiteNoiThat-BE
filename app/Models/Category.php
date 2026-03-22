<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
     protected $table = 'categories';

     protected $fillable = [
        'name',
        'slug',
        'image',
        'parent_id',
        'sort_order',
        'description',
        'created_by',
        'updated_by',
        'status'
    ];

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('sort_order');
    }

     public function products()
    {
        return $this->hasMany(Product::class);
    }

    // public $timestamps = false;
}
