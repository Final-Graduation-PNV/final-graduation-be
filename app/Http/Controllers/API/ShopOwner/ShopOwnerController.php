<?php

namespace App\Http\Controllers\API\ShopOwner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShopOwnerController extends Controller
{
    public function checkAccount(Request $request)
    {
        $id = $request->user()->id;

        $shop = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->where('role_user.user_id', $id)
            ->first('users.*');

        if ($shop && $shop->end_time) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $shop->end_time)->format('Y-m-d');
            $expires = Carbon::now()->format('Y-m-d');

            if ($date === $expires) {
                $delete = DB::table('role_user')
                    ->where('role_id', 2)
                    ->where('user_id', $id)
                    ->delete();
                $expireArray = [
                    'message' => 'Your account has expired. You must pay to continue using!',
                    'name' => $shop->name,
                    'date_used' => $date,
                    'date_expires' => $expires
                ];
                if ($delete) {
                    return response()->json([
                        'valid_account' => [$expireArray]
                    ], 402);
                } else {
                    return response()->json([
                        'valid_account' => [$expireArray]
                    ], 402);
                }
            } else {
                $validArray = [
                    'message' => 'Your account has not expired!',
                    'name' => $shop->name,
                    'date_used' => $date,
                    'date_expires' => $expires
                ];

                return response()->json([
                    'valid_account' => [$validArray]
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'Something went wrong!',
            ], 500);
        }
    }

}
