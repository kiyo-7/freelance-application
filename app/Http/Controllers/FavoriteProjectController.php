<?php

namespace App\Http\Controllers;

use App\Models\FavoriteProject;
use App\Models\Project;
use Illuminate\Http\Request;

class FavoriteProjectController extends Controller
{
    /**
     * List all favorite projects for logged-in freelancer
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'freelancer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $favorites = $user->favoriteProjects()
            ->with('client') // optional
            ->latest('favorite_projects.created_at')
            ->get();

        return response()->json($favorites);
    }

    /**
     * Add project to favorites
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
        ]);

        $user = $request->user();

        if ($user->role !== 'freelancer') {
            return response()->json(['message' => 'Only freelancers can favorite projects'], 403);
        }

        $user->favoriteProjects()->syncWithoutDetaching([
            $request->project_id
        ]);

        return response()->json([
            'message' => 'Project added to favorites'
        ], 201);
    }

    /**
     * Remove project from favorites
     */
    public function destroy(Request $request, $projectId)
    {
        $user = $request->user();

        if ($user->role !== 'freelancer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->favoriteProjects()->detach($projectId);

        return response()->json([
            'message' => 'Project removed from favorites'
        ]);
    }

    /**
     * Check if project is favorited
     */
    public function isFavorited(Request $request, $projectId)
    {
        $user = $request->user();

        $isFavorited = $user->favoriteProjects()
            ->where('project_id', $projectId)
            ->exists();

        return response()->json([
            'is_favorited' => $isFavorited
        ]);
    }
}
