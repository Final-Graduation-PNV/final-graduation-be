<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $table = 'order_details';

    protected $fillable = [
        'order_id',
        'name',
        'phone',
        'city',
        'address',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'id', 'order_id');
    }
}
