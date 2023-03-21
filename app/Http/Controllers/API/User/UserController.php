<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function beShopOwner(Request $request)
    {
        $id = $request->user()->id;

        $data = $request->validate([
            'phone' => 'required',
            'birth' => 'required',
            'gender' => 'required',
            'address' => 'required',
            'city' => 'required'
        ]);

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => "User does not exist!"
            ], 400);
        }

        $user->end_time = Carbon::now()->addMonths(2)->format('Y-m-d');
        $user->phone = $data['phone'];
        $user->birth = $data['birth'];
        $user->gender = $data['gender'];
        $user->address = $data['address'];
        $user->city = $data['city'];
        $user->longitude = $request->longitude;
        $user->latitude = $request->latitude;
        $user->save();

        $role_user = DB::table('role_user')
            ->where('user_id', $id)
            ->where('role_id', 2)
            ->first();

        if ($role_user) {
            return response()->json([
                'message' => "You have already been a shop owner!"
            ], 409);
        }

        DB::table('role_user')->insert(
            array(
                'user_id' => $id,
                'role_id' => 2
            )
        );

        return response()->json([
            'message' => "You are a shop owner now!",
            'user_id' => $id,
            'expires' => $user->end_time
        ], 201);
    }
}
