<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        try {
            $id = $request->user()->id;

            $user = User::where('id', $id)->first(['id', 'name', 'email', 'avatar', 'phone', 'birth', 'address', 'city', 'renewal']);

            return response()->json([
                'user' => $user
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'An error occurred while fetching user data'], 500);
        }
    }

    public function editProfile(Request $request)
    {
        try {
            $id = $request->user()->id;
            $user = User::find($id);

            // If user not found, return error
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $data = $request->only(['name', 'avatar', 'phone', 'birth', 'gender', 'address', 'city']);

            // If no data was submitted, return success message
            if (empty(array_filter($data))) {
                return response()->json(['message' => 'Information has not changed!'], 200);
            }

            // Update user's profile
            $user->fill($data);
            $user->save();

            return response()->json(['message' => 'Profile updated successfully!'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'An error occurred while updating user profile'], 500);
        }
    }

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
