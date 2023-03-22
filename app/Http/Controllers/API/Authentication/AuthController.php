<?php

namespace App\Http\Controllers\API\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Mail\ForgotPassword;
use App\Mail\UserVerification;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     description="Registers a new user account.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Provide user information",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="A Đăm Thanh"),
     *             @OA\Property(property="email", type="string", format="email", example="athanh2002kt@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="@DThanh1508"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="@DThanh1508")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User successfully registered.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Registered, verify your email address to login."),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid or missing user information provided.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid or missing user information provided.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Email already exists and is verified.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email existed.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to send email verification.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Could not send email verification! Please try again.")
     *         )
     *     )
     * )
     *
     * @OA\Schema(
     *     schema="User",
     *     title="User",
     *     description="User object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="A Đăm Thanh"),
     *     @OA\Property(property="email", type="string", format="email", example="athanh2002kt@gmail.com"),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2022-03-20 12:00:00"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", example="2022-03-20 12:00:00")
     * )
     */
    public function register(RegisterRequest $request)
    {
        $validation = $request->validated();

        $user = User::where('email', $validation['email'])->first();

        if ($user) {
            if ($user['email_verified'] == true)
                return response()->json([
                    'message' => 'Email existed',
                ], 401);
            else {
                return response()->json([
                    'message' => 'This api just use for registering the first time.Please use api re_register to reregister',
                ], 400);
            }
        }

        $user = User::create(array_merge(
            [
                'name' => $validation['name'],
                'email' => $validation['email'],
                'password' => bcrypt($validation['password']),
                'confirmation_code' => rand(100000, 999999),
                'confirmation_code_expired_in' => Carbon::now()->addMinutes(5)
            ]
        ));

        try {
            Mail::to($user->email)->send(new UserVerification($user));
            return response()->json([
                'message' => 'Registered,verify your email address to login.',
                'user' => $user
            ], 201);
        } catch (\Exception $err) {
            $user->delete();
            return response()->json([
                'message' => 'Could not send email verification! Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/users/{id}/verify",
     *     summary="Verify user's OTP",
     *     description="Verify the user's OTP and create a token for the user",
     *     tags={"Authentication"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to verify OTP",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="User's OTP",
     *         @OA\JsonContent(
     *             required={"otp"},
     *             @OA\Property(
     *                 property="otp",
     *                 type="integer",
     *                 format="int32",
     *                 minimum=100000,
     *                 example="123456"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully verified",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Successfully verified"
     *             ),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 ref="#/components/schemas/User"
     *             ),
     *             @OA\Property(
     *                 property="token",
     *                 type="string",
     *                 example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid OTP or user does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Your OTP is invalid"
     *             )
     *         )
     *     )
     * )
     */
    public function verifyEmail(Request $request, $id)
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

    /**
     * @OA\Post(
     *     path="/api/users/resend-otp",
     *     summary="Resend OTP to the user",
     *     description="Resend OTP to the user for email verification",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="athanh2002kt@gmail.com"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Registered again, verify your email address to login"),
     *             @OA\Property(property="user", type="object", ref="#/components/schemas/User"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email already verified."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Could not send email verification, please try again."),
     *         ),
     *     ),
     * )
     */
    public function resendOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            if ($user['email_verified'] == true)
                return response()->json([
                    'message' => 'Email existed',
                ], 401);
            else {
                $user->confirmation_code = rand(100000, 999999);
                $user->confirmation_code_expired_in = Carbon::now()->addSecond(60);
                $user->save();
                try {
                    Mail::to($user->email)->send(new UserVerification($user));
                    return response()->json([
                        'message' => 'Registered again,verify your email address to login ',
                        'user' => $user
                    ], 201);
                } catch (\Exception $err) {
                    $user->delete();
                    return response()->json([
                        'message' => 'Could not send email verification,please try again',
                    ], 500);
                }
            }
        }
        return response()->json([
            'message' => 'Failed to re_register',
        ], 500);
    }

    /**
     * @OA\Post(
     *     path="/api/users/{id}",
     *     summary="Cancel user account registration",
     *     description="Cancel a user account registration by deleting the user",
     *     tags={"Authentication"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to cancel",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account registration canceled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Canceled account registration!"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Account deletion failed or user not authorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Account deletion failed!"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthorized"
     *             )
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function cancel($id)
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

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="message", type="string", example="Logged successfully"),
     *             @OA\Property(property="shopOwner", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Incorrect email or password",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Incorrect email or password")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response([
                'message' => 'Incorrect email or password'
            ], 400);
        }

        $role_user = DB::table('role_user')
            ->where('user_id', $user->id)
            ->where('role_id', 2)
            ->first();

        $token = $user->createToken('apiToken')->plainTextToken;

        if ($role_user) {
            $res = [
                'username' => $user->name,
                'id' => $user->id,
                'token' => $token,
                'message' => 'Logged successfully',
                'shopOwner' => true
            ];
        } else {
            $res = [
                'username' => $user->name,
                'id' => $user->id,
                'token' => $token,
                'message' => 'Logged successfully',
                'shopOwner' => false
            ];
        }

        return response($res, 200);
    }

    public function forgotPassword(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => 'required|string'
            ]);

            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                return response([
                    'message' => 'Incorrect email!'
                ], 400);
            }

            $user->confirmation_code = rand(100000, 999999);
            $user->confirmation_code_expired_in = Carbon::now()->addMinutes(5);
            $user->save();
            Mail::to($user->email)->send(new ForgotPassword($user));

            return response()->json([
                'message' => 'OTP has been sent to your email!',
                'id' => $user->id
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while processing your request. Please try again later.'
            ], 500);
        }
    }

    public function verifyOTP(Request $request, $id)
    {
        try {
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
                $user->confirmation_code = null;
                $user->confirmation_code_expired_in = null;
                $user->save();

                return response()->json([
                    'message' => 'OTP verified successfully!',
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while processing your request. Please try again later.'
            ], 500);
        }
    }

    public function changePassword(Request $request, $id)
    {
        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',              // must be at least 8 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ]
        ]);

        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $user->password = bcrypt($request['password']);
            $user->save();
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while processing your request. Please try again later.'
            ], 500);
        }

        return response()->json(['message' => 'Password updated successfully'], 200);
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();
        return [
            'message' => 'user logged out'
        ];
    }
}
