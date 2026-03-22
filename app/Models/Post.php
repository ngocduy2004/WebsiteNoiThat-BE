<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'post';

    protected $fillable = [
        'topic_id',
        'title',
        'slug',
        'image',
        'content',
        'description',
        'post_type',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'status',
    ];

    // public $timestamps = false;

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }

    
}
