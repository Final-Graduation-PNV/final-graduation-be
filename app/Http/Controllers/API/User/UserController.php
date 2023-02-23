<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\BeShopOwnerRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function beShopOwner(BeShopOwnerRequest $request)
    {
        $validation = $request->validated();

        $user = User::find($validation->user_id);
        if (!$user) {
            return response()->json([
                'message' => "User does not exist!"
            ], 400);
        }

        $role_user = DB::table('role_user')
            ->where('user_id', $request->user_id)
            ->where('role_id', 2)
            ->first();

        if ($role_user) {
            return response()->json([
                'message' => "You have already been a shop owner!"
            ], 409);
        }

        DB::table('role_user')->insert(
            array(
                'user_id' => $request->user_id,
                'role_id' => 2
            )
        );

        return response()->json([
            'message' => "You are a shop owner now!",
        ], 201);
    }
}
