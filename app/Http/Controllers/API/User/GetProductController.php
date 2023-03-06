<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class GetProductController extends Controller
{
    public function index()
    {
        $products = Product::all();

        return response()->json([
            'products' => $products
        ], 200);
    }

    public function getById($id)
    {
        $product = Product::find($id);

        $detail = Product::join('users', 'users.id', '=', 'products.shop_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('products.id', $product->id)
            ->get(['users.name as shop_name', 'users.city as address_shop', 'users.id as shop_id', 'products.*', 'categories.name as category_name']);

        return response()->json([
            'product' => $detail
        ], 200);
    }

    public function searchKey(Request $request)
    {
        $key = $request->query('key');
        $products = Product::join('users', 'users.id', '=', 'products.shop_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('products.name', 'like', '%' . $key . '%')
            ->orWhere('users.city', 'like', '%' . $key . '%')
            ->orWhere('categories.name', 'like', '%' . $key . '%')
            ->orWhere('products.description', 'like', '%' . $key . '%')
            ->get([
                'users.name as shop_name',
                'users.city as address_shop',
                'users.id as shop_id',
                'products.*'
            ]);

        return response()->json([
            'products' => $products,
            'message' => 'Search results'
        ], 202);
    }

    public function searchCityCate(Request $request)
    {
        $cate = $request->query('category');
        $city = $request->query('city');
        $products = Product::join('users', 'users.id', '=', 'products.shop_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('categories.name', 'like', '%' . $cate . '%')
            ->where('users.city', 'like', '%' . $city . '%')
            ->get(['products.*']);

        return response()->json([
            'products' => $products,
            'message' => 'Search results'
        ], 202);
    }
}
