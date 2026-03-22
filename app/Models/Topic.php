<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
     protected $table = 'topic';

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'description',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'status',
    ];

    public $timestamps = false;

    public function posts()
    {
        return $this->hasMany(Post::class, 'topic_id');
    }
}
