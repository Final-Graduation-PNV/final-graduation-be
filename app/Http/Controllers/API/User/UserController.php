<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        try {
            $id = $request->user()->id;

            $user = User::where('id', $id)->first(['id', 'name', 'email', 'avatar', 'phone', 'birth', 'gender', 'address', 'city', 'renewal']);

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

    /**
     * Make the current user a shop owner.
     *
     * @param Request $request The HTTP request.
     *
     * @return JsonResponse The HTTP response.
     *
     * @throws InvalidArgumentException If the request data is invalid.
     * @throws Exception If an error occurs while saving the user data or assigning the role.
     */
    public function beShopOwner(Request $request)
    {
        $id = $request->user()->id;

        // Validate the request data.
        $this->validate($request, [
            'name' => 'required|unique:shops,name',
            'phone' => 'required|string|min:10',
            'birth' => 'required|date_format:Y-m-d|before_or_equal:today',
            'gender' => 'required',
            'address' => 'required',
            'city' => 'required'
        ]);

        $endTime = Carbon::now()->addMonth(2)->format('Y-m-d');
        // Create the shop data.
        $shop = new Shop();
        $shop->name = $request->name;
        $shop->phone = $request->phone;
        $shop->birth = $request->birth;
        $shop->gender = $request->gender;
        $shop->address = $request->address;
        $shop->city = $request->city;
        $shop->longitude = $request->longitude;
        $shop->latitude = $request->longitude;
        $shop->user_id = $id;
        $shop->end_time = Carbon::now()->addMonth(2);
        $shop->save();

        // Check if the user already has the shop owner role.
        $hasShopOwnerRole = DB::table('role_user')
            ->where('user_id', $request->user()->id)
            ->where('role_id', 2)
            ->exists();

        if ($hasShopOwnerRole) {
            throw new InvalidArgumentException('You have already been a shop owner!');
        }

        // Assign the shop owner role to the user.
        DB::table('role_user')->insert(
            array(
                'user_id' => $id,
                'role_id' => 2
            )
        );

        // Return a JSON response with the success message.
        return response()->json([
            'message' => 'You are a shop owner now!',
            'shop_id' => $shop->user_id,
            'expires' => $shop->end_time
        ], 201);
    }
}
