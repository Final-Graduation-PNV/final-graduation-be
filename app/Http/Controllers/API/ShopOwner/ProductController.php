<?php

namespace App\Http\Controllers\API\ShopOwner;

use App\Http\Controllers\Controller;
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

    public function create(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'price' => 'required',
            'description' => 'required',
            'image' => 'required',
            'quantity' => 'required|integer|min:1',
            'category_id' => 'required|integer',
            'shop_id' => 'required|integer',
        ]);

        $product = Product::create([
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => $data['description'],
            'image' => $data['image'],
            'quantity' => $data['quantity'],
            'category_id' => $data['category_id'],
            'shop_id' => $data['shop_id'],
        ]);

        $res = [
            'product' => $product,
            'message' => 'Product was created successfully!'
        ];
        return response()->json($res, 201);
    }

    public function update(Request $request, $id)
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
}
