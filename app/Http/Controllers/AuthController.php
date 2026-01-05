<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ClientProfile;
use App\Models\FreelancerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * SIGN UP
     */
    public function signup(Request $request)
    
{
    Log::info('Signup attempt', $request->all());

    $data = $request->validate([
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6',
        'role' => 'required|in:client,freelancer',

        'name' => 'required|string|max:255',
        'location' => 'nullable|string',

        // freelancer-only
        'professional_title' => 'nullable|string',
        'bio' => 'nullable|string',
        'years_of_experience' => 'nullable|integer|min:0',

        // image
        'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
    ]);

    /** Upload avatar if provided */
    $avatarPath = null;
    if ($request->hasFile('avatar')) {
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
    }

    /** 1️⃣ Create user (numeric ID auto-increment) */
    $user = User::create([
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
        'role' => $data['role'],
    ]);

    /** 2️⃣ Create profile based on role — numeric FK only */
    if ($user->role === 'client') {
        ClientProfile::create([
            'user_id' => $user->id, // numeric FK
            'full_name' => $data['name'],
            'location' => $data['location'] ?? null,
            'avatar_url' => $avatarPath ? url('storage/' . $avatarPath) : null,
        ]);
    }

    if ($user->role === 'freelancer') {
        FreelancerProfile::create([
            'user_id' => $user->id, // numeric FK
            'full_name' => $data['name'],
            'professional_title' => $data['professional_title'] ?? '',
            'location' => $data['location'] ?? '',
            'bio' => $data['bio'] ?? '',
            'years_of_experience' => $data['years_of_experience'] ?? 0,
            'response_time' => '24h',
            'avatar_url' => $avatarPath ? url('storage/' . $avatarPath) : null,
        ]);
    }

    /** 3️⃣ Create token */
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'status' => true,
        'message' => 'User registered successfully',
        'token' => $token,
        'token_type' => 'Bearer',
        'user' => $user->load(['clientProfile', 'freelancerProfile']),
    ], 201);
}


    /**
     * LOGIN
     */
    public function login(Request $request)
    {
        Log::info('Login attempt', ['email' => $request->email]);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        $user = Auth::user();
        $token = $request->user()->createToken('auth_token')->plainTextToken;


        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 200);
    }

    /**
     * VALIDATE TOKEN
     */
    public function validateToken(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'valid' => false,
                'message' => 'No token provided'
            ], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid token'
            ], 401);
        }

        return response()->json([
            'valid' => true,
            'user' => $accessToken->tokenable->load(['clientProfile', 'freelancerProfile']),
            'abilities' => $accessToken->abilities,
        ]);
    }

    /**
     * LOGOUT
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
