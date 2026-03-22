<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Order extends Model
{
     protected $table = 'order';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'payment_method',
        'note',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'status',
    ];

    public $timestamps = false;

    public function details()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
