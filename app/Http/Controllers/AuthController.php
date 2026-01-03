<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
 public function signup(Request $request)
{
    // Validate request
    $data = $request->validate([
        'email' => 'required|email|unique:authusers,email',
        'password' => 'required|string|min:6',
        'role' => 'required|in:client,freelancer,admin',

        // profile fields
        'name' => 'required|string|max:255',
        'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        'skills' => 'nullable|string',
        'portfolio' => 'nullable|string',
    ]);

    // 1️⃣ Handle profile image upload
    if ($request->hasFile('profile_image')) {
        $path = $request->file('profile_image')->store('profile_images', 'public');
        $data['profile_image'] = $path;
    }

    // 2️⃣ Create authuser
    $user = User::create([
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
        'role' => $data['role'],
    ]);

    // 3️⃣ Create profile with SAME user_id
    $profile = UserProfile::create([
        'user_id' => $user->id,
        'name' => $data['name'],
        'profile_image' => $data['profile_image'] ?? null,
        'skills' => $data['skills'] ?? null,
        'portfolio' => $data['portfolio'] ?? null,
    ]);

    // 4️⃣ Add full URL for profile_image
    if ($profile->profile_image) {
        $profile->image_url = url('storage/' . $profile->profile_image);
    }

    $token = $request->user()->createToken('auth_token')->plainTextToken;

    return response()->json([
        'status' => true,
        'message' => 'User registered successfully',
        'user' => $user,
        'profile' => $profile,
    ], 201);
    
}


    public function login(Request $request)
    {
        // Validate requesth
   Log::info('Login email:', ['email' => $request]);
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

     public function logout(Request $request)
    {
    // Delete the current access token
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'status' => true,
        'message' => 'Logged out successfully',
    ], 200);
    }

}
