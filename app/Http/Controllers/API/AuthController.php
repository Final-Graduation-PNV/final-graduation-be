<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request){
        $validation  = $request->validated();
        $user = User::create([
            'name'     => $validation['name'],
            'email'    => $validation['email'],
            'password' => bcrypt($validation['password']),
            'address'  => $validation['address'],
            'city'     => $validation['city']
        ]);

        DB::table('role_user')->insert([
            'role_id' => 3,
            'user_id' => $user->id
        ]);

        $token = $user->createToken('apiToken', ['server:update'])->plainTextToken;

        $res = [
            'user' => $user,
            'token' => $token
        ];
        return response($res, 201);
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

        if ($role_user)
        {
            $res = [
                'username' => $user->name,
                'id' => $user->id,
                'token' => $token,
                'message' => 'Logged successfully',
                'shop owner' => true
            ];
        }
        else
        {
            $res = [
                'username' => $user->name,
                'id' => $user->id,
                'token' => $token,
                'message' => 'Logged successfully',
                'shop owner' => false
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
