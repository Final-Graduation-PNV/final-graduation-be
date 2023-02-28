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

        $product = Product::find($data['id']);

        if (!$product) {
            return response()->json([
                'message' => 'Product was not found!',
            ], 404);
        }

        $cartProduct = Product::where('id', $data['product_id'])->first();
        echo $cartProduct;

        if ($cartProduct) {
            if (!($cartProduct->user_id == $request->user()->id)) {
                return response()->json([
                    'message' => 'Permission issue!',
                ], 403);
            }
            $data['quantity'] += $cartProduct->quantity;
        }

        if ($product->quantity < $data['quantity']) {
            return response()->json([
                'message' => 'The quantity must be less or equal than product quantity',
            ], 400);
        }
        try {
            if ($cartProduct) {
                $cartProduct->quantity = $data['quantity'];
                $cartProduct->save();
                return response()->json([
                    'message' => 'Your carts was updated successfully!',
                    'cart' => $cartProduct
                ], 200);
            } else {
                $cart = new Cart();
                $cart->product_id = $data['product_id'];
                $cart->user_id = $request->user()->id; // get user_id from logged in user
                $cart->quantity = $data['quantity'];
                $cart->save();
                return response()->json([
                    'message' => 'Your action was done successfully!',
                    'cart' => $cart
                ], 201);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!'
            ], 500);
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
            $carts = Cart::where('user_id', $user_id)->get();
            foreach ($carts as $cart) {
                $cart->product;
            }
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
        $product = Post::find($cart->post_id);
        if ($product->likes < $data['quantity']) {
            return response()->json([
                'message' => 'The quantity must be less or equal than product quantity',
            ], 400);
        }
        try {
            $cart->quantity = $data['quantity'];
            $cart->save();
            return response()->json([
                'message' => 'Cart was updated successfully!',
                'cart' => $cart
            ], 200);
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
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!'
            ], 500);
        }

    }

    function deleteMany(Request $request): JsonResponse
    {
        try {
            $carts = Cart::whereIn('id', $request->ids)->get();
            foreach ($carts as $cart) {
                echo $request->user()->id;
                echo $cart->user_id;

                if (!($cart->user_id == $request->user()->id)) {
                    return response()->json([
                        'message' => 'Permission issue!',

                    ], 403);
                }
            }
            $carts->delete();
            return response()->json([
                'message' => 'Carts were deleted successfully!',
            ], 200);
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
