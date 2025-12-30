<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        // Validate request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Response
        return response()->json([
            'status' => true,
            'message' => 'User registered successfully',
            'data' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        // Validate request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Attempt login
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        // Get authenticated user
        $user = Auth::user();
            $token = $request->user()->createToken('auth_token')->plainTextToken;
        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token,
            'token_type' => 'Bearer',
            'data' => $user,
        ], 200);
    }

     public function validateToken(Request $request)
    {
        // Extract token from Authorization header
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['valid' => false, 'message' => 'No token provided'], 401);
        }

        // Find the token in database
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['valid' => false, 'message' => 'Invalid token'], 401);
        }

        // Return token info and associated user
        return response()->json([
            'valid' => true,
            'user' => $accessToken->tokenable,
            'abilities' => $accessToken->abilities,
        ]);
    }
}
