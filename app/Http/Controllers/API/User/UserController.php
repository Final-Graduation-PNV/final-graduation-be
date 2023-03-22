<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\RoleUser;
use App\Models\Shop;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class UserController extends Controller
{
    public function getNearbyProducts($latitude, $longitude)
    {
        $radius = 6371; // Earth's radius in km
        $maxDistance = 100; // Maximum distance to search in km
        $products = Product::selectRaw('*, (' . $radius . ' * acos(cos(radians(' . $latitude . ')) * cos(radians(latitude)) * cos(radians(longitude) - radians(' . $longitude . ')) + sin(radians(' . $latitude . ')) * sin(radians(latitude)))) AS distance')
            ->having('distance', '<', $maxDistance)
            ->orderBy('distance')
            ->get();
        return response()->json($products);
    }
    
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
        $data = $request->validate([
            'name' => 'required|unique:shops,name',
            'phone' => 'required|string|min:10',
            'birth' => 'required|date_format:Y-m-d|before_or_equal:today',
            'gender' => 'required',
            'address' => 'required',
            'city' => 'required'
        ]);

        // Create the shop data.
        $shop = Shop::create(array_merge(
            [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'birth' => $data['birth'],
                'gender' => $data['gender'],
                'address' => $data['address'],
                'city' => $data['city'],
                'longitude' => $request->longitude,
                'latitude' => $request->latitude,
                'user_id' => $id,
                'end_time' => Carbon::now()->addMonths(2)->format('Y-m-d')
            ]
        ));

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
            'shop_id' => $shop->id,
            'expires' => $shop->end_time
        ], 201);
    }
}
