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
    function add(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product was not found!',
            ], 404);
        }

        $cartProduct = Product::where('id', $id)->first();

        $addedCart = Cart::where('user_id', $request->user()->id)
            ->where('product_id', $id)
            ->first();

        $pro = $product->quantity;

        if ($addedCart) {
            $cart = $data['quantity'] + $addedCart->quantity;
            if ($pro < $cart) {
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
                $cart->product_id = $id;
                $cart->user_id = $request->user()->id; // get user_id from logged in user
                $cart->quantity = $data['quantity'];
                $cart->save();

                return response()->json([
                    'message' => 'Add to cart successfully!',
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
        try {
            $carts = Cart::join('products', 'products.id', '=', 'carts.product_id')
                ->where('carts.user_id', $user_id)
                ->where('carts.status', false)
                ->get(['carts.id as cart_id',
                    'products.name',
                    'products.image',
                    'products.price',
                    'products.description',
                    'carts.quantity as cart_quantity']);
            return response()->json([
                'carts' => $carts
            ], 200);
        } catch (Exception $exception) {
            return response()->json([
                'message' => 'Something went wrong!'
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    function updateQuantity(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json([
                'message' => 'Cart was not found!',
            ], 404);
        }

        if (!($cart->user_id == $request->user()->id)) {
            return response()->json([
                'message' => 'Permission issue!',
            ], 403);
        }

        $product = Product::find($cart->product_id);

        if ($product->quantity < $data['quantity']) {
            return response()->json([
                'message' => 'Your cart! The quantity must be less or equal than product quantity',
            ], 400);
        }
        try {
            $cart->quantity = $data['quantity'];
            $cart->save();
            return response()->json([
                'message' => 'Cart was updated successfully!',
                'cart' => $cart
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!'
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    function deleteCart(Request $request, $id): JsonResponse
    {
        $cart = Cart::find($id);
        if (!$cart) {
            return response()->json([
                'message' => 'Cart was not found!',
            ], 404);
        }
        if (!($cart->user_id == $request->user()->id)) {
            return response()->json([
                'message' => 'Permission issue!',
            ], 403);
        }
        try {
            $cart->delete();
            return response()->json([
                'message' => 'Cart was deleted successfully!',
                'cart' => $cart
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!'
            ], 500);
        }

    }

    function deleteMany(Request $request): JsonResponse
    {
        try {
            $carts = Cart::where('user_id', $request->user()->id)->first();
            if (!$carts) {
                return response()->json([
                    'message' => 'Permission issue!',
                ], 403);
            }

            $carts = Cart::whereIn('id', $request->ids)->delete();
            return response()->json([
                'message' => 'Carts were deleted successfully!',
                'carts' => $carts
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!'
            ], 500);
        }
    }

    function clear(Request $request): JsonResponse
    {
        try {
            Cart::where('user_id', $request->user()->id)->delete();
            return response()->json([
                'message' => 'Carts were deleted successfully!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!'
            ], 500);
        }
    }
}

