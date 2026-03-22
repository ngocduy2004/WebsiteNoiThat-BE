<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductAttribute;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function productAttributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }
}
