<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartDetail extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'cart_id',
        'name',
        'phone',
        'city',
        'address',
        'note',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'id', 'cart_id');
    }
}
