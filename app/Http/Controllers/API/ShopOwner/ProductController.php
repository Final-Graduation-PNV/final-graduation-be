<?php

namespace App\Http\Controllers\API\ShopOwner;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|integer|min:1'
        ]);

        $shop = User::find($request->shop_id);
        if (!$shop) {
            return response()->json([
                'message' => 'Shop does not exist!'
            ], 400);
        }

        return Product::join('categories', 'categories.id', '=', 'products.category_id')
            ->where('products.shop_id', $shop->id)
            ->get(['categories.name as category_name', 'products.*']);
    }

    public function getById($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'message' => 'Product was not found!'
            ], 404);
        }

        $product->category;

        return response()->json([
            'product' => $product
        ], 200);
    }

    public function create(CreateProductRequest $request)
    {
        $validation = $request->validated();

        $product = Product::create([
            'name' => $validation['name'],
            'price' => $validation['price'],
            'description' => $validation['description'],
            'image' => $validation['image'],
            'quantity' => $validation['quantity'],
            'category_id' => $validation['category_id'],
            'shop_id' => $validation['shop_id'],
        ]);

        $res = [
            'product' => $product,
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
        ]);

        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'message' => 'Product was not found!'
            ], 404);
        }

        if ($request->shop_id) {
            return response()->json([
                'message' => 'You can not update the shop owner!'
            ], 400);
        }

        if ($request->category_id) {
            return response()->json([
                'message' => 'You can not update the category!'
            ], 400);
        }

        $product->update($request->all());

        return response()->json([
            'product' => $product
        ], 200);
    }

    public function destroy($id)
    {
        return Product::destroy($id);
    }

    public function search($name)
    {
        return Product::where('name', 'like', '%' . $name . '%')->get();
    }
}
