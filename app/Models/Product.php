<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'price',
        'description',
        'image',
        'quantity',
        'category_id',
        'shop_id'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function shop()
    {
        return $this->belongsTo(User::class,'shop_id','id');
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class,'product_id','id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'product_id', 'id');
    }
}
