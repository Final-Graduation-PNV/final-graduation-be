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
}
