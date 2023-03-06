<?php

namespace App\Http\Controllers\API\ShopOwner;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $id = $request->user()->id;

        if (!$id) {
            return response()->json([
                'message' => 'Does not your account!'
            ], 404);
        }

        $products = Product::join('categories', 'categories.id', '=', 'products.category_id')
            ->where('products.shop_id', $id)
            ->get([
                'categories.name as category_name',
                'products.*'
            ]);

        return response()->json([
            'products' => $products
        ], 200);
    }

    public function getById(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product was not found!'
            ], 404);
        }

        $idd = $request->user()->id;

        if (!$idd) {
            return response()->json([
                'message' => 'Does not your account!'
            ], 404);
        }

        $product = Product::join('categories', 'categories.id', '=', 'products.category_id')
            ->join('users', 'users.id', '=', 'products.shop_id')
            ->where('products.id', $id)
            ->where('products.shop_id', $idd)
            ->get([
                'users.name as shop_name',
                'users.city as shop_city',
                'categories.name as category_name',
                'products.*'
            ]);

        return response()->json([
            'product' => $product
        ], 200);
    }

    public function create(CreateProductRequest $request)
    {
        $validation = $request->validated();

        $shop = User::find($request->shop_id);

        if (!$shop) {
            return response()->json([
                'message' => 'Shop does not exist!'
            ], 400);
        }

        $id = $request->user()->id === $shop->id;

        if (!$id) {
            return response()->json([
                'message' => 'Does not your account!'
            ], 404);
        }

        $product = Product::create([
            'name' => $validation['name'],
            'price' => $validation['price'],
            'description' => $validation['description'],
            'image' => $validation['image'],
            'quantity' => $validation['quantity'],
            'category_id' => $validation['category_id'],
            'shop_id' => $validation['shop_id'],
        ]);

        $cate = Product::join('categories', 'categories.id', '=', 'products.category_id')
            ->where('products.id', $product->id)
            ->get(['products.*', 'categories.name as category_name']);

        $res = [
            'product' => $cate,
            'message' => 'Product was created successfully!'
        ];

        return response()->json($res, 201);
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required',
            'description' => 'required',
            'image' => 'required',
            'quantity' => 'required|integer|min:1',
            'category_id' => 'required|integer',
        ]);

        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'message' => 'Product was not found!'
            ], 404);
        }

        $id = $request->user()->id;

        if (!$id) {
            return response()->json([
                'message' => 'Does not your account!'
            ], 404);
        }

        if ($request->shop_id) {
            return response()->json([
                'message' => 'You can not update the shop owner!'
            ], 400);
        }

        $product->update($request->all());

        return response()->json([
            'product' => $product,
            'message' => 'Product was updated successfully!'
        ], 201);
    }

    public function destroy($id)
    {
        $product = Product::destroy($id);

        return response()->json([
            'product' => $product,
            'message' => 'Product was deleted successfully!'
        ], 200);
    }

    public function search(Request $request)
    {
        $id = $request->user()->id;

        if (!$id) {
            return response()->json([
                'message' => 'Does not your account!'
            ], 404);
        }

        $name = $request->query('name');
        $products = Product::join('categories', 'categories.id', '=', 'products.category_id')
            ->where('products.name', 'like', '%' . $name . '%')
            ->where('products.shop_id', $id)
            ->get(['products.*']);

        return response()->json([
            'product' => $products,
            'message' => 'Search results'
        ], 202);
    }

    public function category()
    {
        $categories = Category::all();

        return response()->json([
            'categories' => $categories,
        ], 200);
    }
}
