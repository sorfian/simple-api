<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Hash;
use App\User;
use Auth;

class AuthController extends Controller
{
    // Register
   
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $user = $request->all();
        $user['password'] = Hash::make($user['password']);
        $user = User::create($user);
        $success['token'] = $user->createToken('authToken')->accessToken;
        $success['name'] = $user->name;

        return response()->json(['success' => $success]);
    }

    // Login

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = request(['email', 'password']);
        if (Auth::attempt($credentials)){
            $user = $request->user();
            $data['token'] = $user->createToken('authToken')->accessToken;
            $data['name'] = $user->name;
            $data['id'] = $user->id;
            return response()->json($data,200);
        }
       
        return response()->json([
            'error' => 'Unauthorized', 
            'data' => $validator->errors()
        ], 401);
    }

    // Logout

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
