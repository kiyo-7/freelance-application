<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class FavoriteProjectController extends Controller
{
    /**
     * Toggle favorite project
     * POST /projects/{projectId}/favorite
     */
    public function toggle(Request $request, $projectId)
    {
        $user = $request->user();

        if ($user->role !== 'freelancer') {
            return response()->json([
                'message' => 'Only freelancers can favorite projects'
            ], 403);
        }

        $project = Project::findOrFail($projectId);

        $isFavorited = $user->favoriteProjects()
            ->where('project_id', $projectId)
            ->exists();

        if ($isFavorited) {
            $user->favoriteProjects()->detach($projectId);

            return response()->json([
                'is_favorited' => false,
                'message' => 'Project removed from favorites'
            ]);
        }

        $user->favoriteProjects()->attach($projectId);

        return response()->json([
            'is_favorited' => true,
            'message' => 'Project added to favorites'
        ], 201);
    }

    /**
     * Get all favorite projects (FV)
     * GET /projects/favorites
     */
    public function indexFavorites(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'freelancer') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $projects = $user->favoriteProjects()
            ->with('client:id,email') // project owner
            ->latest('favorite_projects.created_at')
            ->get()
            ->map(function ($project) {
                $project->is_favorited = true;
                return $project;
            });

        return response()->json([
            'status' => 200,
            'data' => $projects
        ]);
    }
}
