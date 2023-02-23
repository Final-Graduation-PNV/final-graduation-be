<?php

namespace App\Http\Controllers\API\ShopOwner;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all();
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
        $validation = $request->validated();

        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'message' => 'Product was not found!'
            ], 404);
        }

        if ($validation->shop_id) {
            return response()->json([
                'message' => 'You can not update the shop owner!'
            ], 400);
        }

        if ($validation->category_id) {
            return response()->json([
                'message' => 'You can not update the category!'
            ], 400);
        }

        $product->update($validation->all());

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
        return  Product::where('name', 'like', '%' . $name . '%')->get();
    }
}
