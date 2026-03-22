<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'site_name',
        'email',
        'phone',
        'hotline',
        'address',
        'status',
    ];

    public $timestamps = false;
}
