<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery\Exception;

class CartController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    function add(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::find($data['product_id']);

        if (!$product) {
            return response()->json([
                'message' => 'Product was not found!',
            ], 404);
        }

        $cartProduct = Product::where('id', $data['product_id'])->first();

        $addedCart = Cart::where('user_id', $request->user()->id)
            ->where('product_id', $data['product_id'])
            ->first();

        if ($addedCart) {
            if ($product->quantity < ($data['quantity'] + $product->quantity)) {
                return response()->json([
                    'message' => 'Your cart! The quantity must be less or equal than product quantity',
                ], 400);
            }

            $addedCart->quantity += $data['quantity'];

            $addedCart->save();

            return response()->json([
                'message' => 'Your shopping cart has been updated!',
                'cart' => $addedCart
            ], 201);
        }

        switch ($cartProduct) {
            case $cartProduct->shop_id == $request->user()->id:
                return response()->json([
                    'message' => 'You can not add your product!',
                ], 403);
            case $product->quantity < $data['quantity']:
                return response()->json([
                    'message' => 'The quantity must be less or equal than product quantity',
                ], 400);
            default:
                $cart = new Cart();
                $cart->product_id = $data['product_id'];
                $cart->user_id = $request->user()->id; // get user_id from logged in user
                $cart->quantity = $data['quantity'];
                $cart->save();
                return response()->json([
                    'message' => 'Add cart successfully!',
                    'cart' => $cart
                ], 201);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    function getCartByUserId(Request $request): JsonResponse
    {
        $user_id = $request->user()->id;
        $products = [];
        try {
            $carts = Cart::where('user_id', $user_id)->get();
            foreach ($carts as $cart) {
                $products[] = $cart->product;
            }
            return response()->json([
                'carts' => $products
            ], 200);
        } catch (Exception $exception) {
            return response()->json([
                'message' => 'Something went wrong!'
            ], 500);
        }

    }
}
