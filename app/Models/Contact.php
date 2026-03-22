<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
     protected $table = 'contact';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'content',
        'reply_id',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'status',
    ];

    public $timestamps = false;
}
