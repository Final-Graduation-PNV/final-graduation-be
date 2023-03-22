<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Mail\UserBill;
use App\Mail\UserVerification;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Mockery\Exception;

class PaymentController extends Controller
{
    public function showAmount(Request $request)
    {
        try {
            $updates = Cart::join('products', 'products.id', '=', 'carts.product_id')
                ->whereIn('carts.id', $request->ids)
                ->get(['carts.id',
                    'carts.quantity',
                    'carts.amount',
                    'products.price as product_price']);
            foreach ($updates as $cart) {
                $amount = Cart::find($cart->id);
                $amount->amount = $cart->product_price * $cart->quantity;
                $amount->save();
            }
            $carts = Cart::join('products', 'products.id', '=', 'carts.product_id')
                ->join('users', 'users.id', '=', 'carts.user_id')
                ->where('carts.user_id', $request->user()->id)
                ->whereIn('carts.id', $request->ids)
                ->get(['users.name as user_name',
                    'users.email as user_email',
                    'users.phone as user_phone',
                    'users.address as user_address',
                    'users.city as user_city',
                    'carts.id as cart_id',
                    'carts.quantity as cart_quantity',
                    'carts.amount as cart_amount',
                    'carts.status as cart_status',
                    'carts.note as cart_note',
                    'products.id as product_id',
                    'products.name as product_name',
                    'products.image as product_image',
                    'products.price as product_price'
                ]);
            if (!$carts) {
                return response()->json([
                    'message' => 'Permission issue!',
                ], 403);
            }
            return response()->json([
                'paying' => $carts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!'
            ], 500);
        }
    }

    private function updateCart(Cart $cart, array $data, User $user)
    {
        $cart->note = $data['note'] ?? 'Please deliver on time!';
        $cart->name = $data['name'] ?? $user->name;
        $cart->phone = $data['phone'] ?? $user->phone;
        $cart->city = $data['city'] ?? $user->city;
        $cart->address = $data['address'] ?? $user->address;
        $cart->status = true;
        $cart->update();
    }

    private function getPaymentDetails(Request $request)
    {
        $cartData = Cart::with(['product', 'user'])
            ->where('user_id', $request->user()->id)
            ->whereIn('id', $request->ids)
            ->get();

        $outPut = [];
        foreach ($cartData as $cart) {
            $outPut[] = [
                'cart_id' => $cart->id,
                'user_name' => $cart->name,
                'user_email' => $cart->user->email,
                'user_phone' => $cart->phone,
                'user_address' => $cart->address,
                'user_city' => $cart->city,
                'cart_quantity' => $cart->quantity,
                'cart_amount' => $cart->amount,
                'cart_note' => $cart->note,
                'product_name' => $cart->product->name,
                'product_price' => $cart->product->price,
            ];
        }
        return $outPut;
    }

    public function payment(Request $request)
    {
        $data = $request->only(['ids', 'note', 'name', 'phone', 'city', 'address']);

        $carts = Cart::with('product')
            ->find($data['ids']);

        $user = $request->user();

        foreach ($carts as $cart) {
            $this->updateCart($cart, $data, $user);
        }

        $payment = $this->getPaymentDetails($request);
        Mail::to($user->email)->send(new UserBill($payment));

        return response()->json([
            'message' => 'Check your email( ' . $user->email . ') to see bill detail.'
        ], 200);
    }
}
