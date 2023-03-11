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

    public function payment(Request $request)
    {
        $data = $request->only(['ids', 'note', 'name', 'phone', 'city', 'address']);

        $carts = Cart::find($data['ids']);

        $user = User::where('id', $request->user()->id)->get();

        foreach ($carts as $cart) {
            if (!is_null($data['note'] or $data['name'] or $data['phone'] or $data['city'] or $data['address'])) {
                $cart->note = "Please deliver on time!";
                $cart->name = $user[0]['name'];
                $cart->phone = $user[0]['phone'];
                $cart->city = $user[0]['city'];
                $cart->address = $user[0]['address'];
            } else {
                $cart->note = $data['note'];
                $cart->name = $data['name'];
                $cart->phone = $data['phone'];
                $cart->city = $data['city'];
                $cart->address = $data['address'];
            }
            $cart->status = true;
            $cart->save();
        }
        $data = Cart::join('products', 'products.id', '=', 'carts.product_id')
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
        Mail::to($user[0]['email'])->send(new UserBill($data));
        return response()->json([
            'message' => 'Check your email address to see bill detail.'
        ], 201);
    }
}
