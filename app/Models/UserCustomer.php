<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // ✅ đây là key
use Tymon\JWTAuth\Contracts\JWTSubject;

class UserCustomer extends Authenticatable implements JWTSubject
{
    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'username',
        'password',
        'roles',
        'avatar',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'status',
    ];

    protected $hidden = ['password'];

    public $timestamps = false;

    // ================= JWT =================
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
