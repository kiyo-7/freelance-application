<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json($user->profile);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($user->profile) {
            return response()->json(['message' => 'Profile already exists'], 400);
        }

        $data = $request->validate([
            'name' => 'required|string',
            'profile_image' => 'nullable|string',
            'skills' => 'nullable|string',
            'portfolio' => 'nullable|string',
        ]);

        return response()->json(
            $user->profile->create($data),
            201
        );
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|string',
            'profile_image' => 'sometimes|nullable|string',
            'skills' => 'sometimes|nullable|string',
            'portfolio' => 'sometimes|nullable|string',
        ]);

        $user->profile->update($data);

        return response()->json(['message' => 'Profile updated']);
    }
}
