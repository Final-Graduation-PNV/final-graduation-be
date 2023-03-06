<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    public function verifyOTP(Request $request, $id)
    {
        $data = $request->validate([
            'otp' => 'required|integer|min:100000'
        ]);

        $user = User::findOrFail($id);

        if (!$user) {
            return response()->json(["message" => "User does not exist"], 400);
        } elseif (Carbon::now()->gt($user->confirmation_code_expired_in)) {
            return response()->json(["message" => "Your OTP expired"], 400);
        } else {
            if ($data['otp'] != $user->confirmation_code) {
                return response()->json(["message" => "Your OTP is invalid"], 400);
            }

            $user->email_verified = true;
            $user->save();

            DB::table('role_user')->insert([
                'role_id' => 3,
                'user_id' => $user->id
            ]);

            $token = $user->createToken('apiToken', ['server:update'])->plainTextToken;

            $res = [
                'message' => "Successfully verified",
                'user' => $user,
                'token' => $token
            ];

            return response()->json($res, 201);
        }
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if ($user->email_verified == false) {
            $result = $user->delete();
            if ($result)
                return response()->json(["message" => "Canceled account registration!"], 200);
            else {
                return response()->json(["message" => "Account deletion failed!"], 400);
            }
        }
        return response()->json(["message" => "Unauthorized"], 400);
    }
}
