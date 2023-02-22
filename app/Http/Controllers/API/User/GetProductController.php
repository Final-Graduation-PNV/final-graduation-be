<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class GetProductController extends Controller
{
    public function search(Request $request)
    {
        $key=$request->query('key');
        $city=$request->query('city');
        return Product::join('users', 'users.id', '=', 'products.shop_id')
            ->where('users.city', $city)
            ->where('products.name','like','%'.$key.'%')
            ->orWhere('products.description','like','%'.$key.'%')
            ->get(['users.name as shop_name','users.id as shop_id','products.*']);
    }
}
