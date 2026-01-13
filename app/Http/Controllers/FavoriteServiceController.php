<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class FavoriteServiceController extends Controller
{
    /**
     * Toggle favorite service
     * POST /services/{serviceId}
     */
    public function toggle(Request $request, $serviceId)
    {
        $user = $request->user();

        $service = Service::findOrFail($serviceId);

        $isFavorited = $user->favoriteServices()
            ->where('service_id', $serviceId)
            ->exists();

        if ($isFavorited) {
            $user->favoriteServices()->detach($serviceId);

            return response()->json([
                'is_favorited' => false,
                'message' => 'Service removed from favorites'
            ]);
        }

        $user->favoriteServices()->attach($serviceId);

        return response()->json([
            'is_favorited' => true,
            'message' => 'Service added to favorites'
        ], 201);
    }

    /**
     * Get all favorite services (FV)
     * GET /services/favorites
     */
    public function indexFavorites(Request $request)
    {
        $user = $request->user();

        $services = $user->favoriteServices()
        ->with([
        'user:id,email',
        'user.freelancerProfile:id,user_id,full_name,avatar_url,rating'
    ])            ->latest('favorite_services.created_at')
            ->get()
            ->map(function ($service) {
                $service->is_favorited = true; // always true here
                return $service;
            });

        return response()->json([
            'status' => 200,
            'data' => $services
        ]);
    }
}
