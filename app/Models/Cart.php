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
        'name',
        'phone',
        'city',
        'address',
        'note',
        'total_price',
        'status',
    ];

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
}
