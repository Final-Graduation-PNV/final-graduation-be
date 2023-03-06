<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Mail\UserVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
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
                'confirmation_code_expired_in' => Carbon::now()->addMinutes(2)
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

    public function reregister(Request $request)
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

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();
        return [
            'message' => 'user logged out'
        ];
    }
}
