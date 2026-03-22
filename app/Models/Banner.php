<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'banner';

    protected $fillable = [
        'name',
        'image',
        'link',
        'position',
        'sort_order',
        'description',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'status',
    ];

    public $timestamps = false;


    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image
            ? url($this->image)
            : null;
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

}
