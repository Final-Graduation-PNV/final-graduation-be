<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $post_id
 * @property mixed $user_id
 * @property mixed $quantity
 * @method static where(string $string, $user_id)
 */
class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
    ];

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function cartDetail()
    {
        return $this->hasOne(CartDetail::class,'cart_id','id');
    }
}
