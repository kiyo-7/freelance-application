<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientProfileController extends Controller
{
  

    public function show(Request $request)
    {



        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check role
        if ($user->role !== 'client') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $profile = $user->clientProfile;
                Log::info($profile);

        return response()->json([
            'status' => 200,
            'profile' => $profile,
        ]);
    }

    /**
     * Update authenticated client profile
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role !== 'client') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $profile = $user->clientProfile;

        if (! $profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $data = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'location' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar_url'] = url('storage/' . $path);
        }

        $profile->update($data);

        return response()->json([
            'status' => 200,
            'profile' => $profile,
        ]);
    }
}
