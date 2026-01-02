<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserProfile;

class UserProfileController extends Controller
{
    /**
     * Show authenticated user's profile
     */
    public function show()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json($user->profile);
    }

    /**
     * Create a profile for authenticated user
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Prevent duplicate profiles
        if ($user->profile) {
            return response()->json(['message' => 'Profile already exists'], 400);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'profile_image' => 'nullable|string',
            'skills' => 'nullable|string',
            'portfolio' => 'nullable|string',
        ]);

        $profile = UserProfile::create($data);

        return response()->json([
            'message' => 'Profile created successfully',
            'profile' => $profile
        ], 201);
    }

    /**
     * Update authenticated user's profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'profile_image' => 'sometimes|nullable|string',
            'skills' => 'sometimes|nullable|string',
            'portfolio' => 'sometimes|nullable|string',
        ]);

        $user->profile->update($data);

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => $user->profile
        ]);
    }
}
