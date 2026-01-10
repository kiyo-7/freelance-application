<?php

namespace App\Http\Controllers;

use App\Models\FreelancerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FreelancerProfileController extends Controller
{

    public function index_all()
    {
        // fetching all freelancers
        $freelancers = FreelancerProfile::with('user')->get();

        Log::info(' Fetching all freelancers');
        Log::info(' Freelancers data: ' . json_encode($freelancers));
        return response()->json([
        'status' => 200,
        'data' => $freelancers]);
        
    }

    public function show(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();



        if ($user->role !== 'freelancer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $profile = $user->freelancerProfile;

        return response()->json([
            'status' => 200,
            'user'=> $user,
        ]);
    }

    /**
     * Update authenticated freelancer profile
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        Log::info('the body of request: ' . json_encode($request->all()));

        if ($user->role !== 'freelancer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $profile = $user->freelancerProfile;
 

        if (! $profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $data = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'professional_title' => 'sometimes|string|max:255',
            'location' => 'sometimes|string|max:255',
            'bio' => 'sometimes|string',
            'hourlyRate' => 'sometimes|numeric|max:50000',
            'years_of_experience' => 'sometimes|integer|min:0',
            'response_time' => 'sometimes|string|max:50',
            'languages' => 'sometimes|array',
            'skills' => 'sometimes|array',
            'services' => 'sometimes|array',
            'portfolio' => 'sometimes|array',
            'reviews' => 'sometimes|array',
            'rating_distribution' => 'sometimes|array',

            // Avatar
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar_url'] = url('storage/' . $path);
        }

        $profile->update($data);

        Log::info('Updated profile data: ' . json_encode($data));

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'profile' => $profile,
        ]);
    }
}
