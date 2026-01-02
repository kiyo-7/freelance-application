<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

        $profile = $user->profile;

        if ($profile && $profile->profile_image) {
            $profile->image_url = url('storage/' . $profile->profile_image);
        }

        return response()->json($profile);
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
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'skills' => 'nullable|string',
            'portfolio' => 'nullable|string',
        ]);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $data['profile_image'] = $path;
        }

        $profile = UserProfile::create(array_merge($data, ['user_id' => $user->id]));

        if ($profile->profile_image) {
            $profile->image_url = url('storage/' . $profile->profile_image);
        }

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
            'profile_image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'skills' => 'sometimes|nullable|string',
            'portfolio' => 'sometimes|nullable|string',
        ]);

        // Handle image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($user->profile->profile_image) {
                Storage::disk('public')->delete($user->profile->profile_image);
            }
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $data['profile_image'] = $path;
        }

        $user->profile->update($data);

        if ($user->profile->profile_image) {
            $user->profile->image_url = url('storage/' . $user->profile->profile_image);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => $user->profile
        ]);
    }
}
