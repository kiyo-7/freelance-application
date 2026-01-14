<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{


    public function index_all()
    {
        $projects = Project::with('client')->latest()->get();
        return response()->json([
            'status' => 200,
            'data' => $projects]);
    }

    /**
     * List all projects with client info
     */
    public function index()
    {
          Log::info(' Fetching all projects');
        return Project::with('client')->latest()->get();
        return response()->json(['data' => $projects]);
    }

    /**
     * Store a new project
     */
    public function store(Request $request)
    {

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'budget' => 'required|numeric',
            'location' => 'nullable|string|max:255',
            'category_badge' => 'nullable|string|max:255',
            'posted_at' => 'nullable|date',
        ]);

        $user = Auth::user();

        // Required relations
        $data['client_id'] = $user->id;
        $data['client_name'] = $user->name ?? 'Client'; // fallback if name 

        // Default posted_at to now if not provided
        $data['posted_at'] = $data['posted_at'] ?? now();

        $project = Project::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Project created successfully',
            'project' => $project->load('client')
        ], 201);
    }

    /**
     * Show a single project with applications and freelancer info
     */
public function show(Project $project)
{
    return response()->json([
        'data' => $project
            ->load('proposals.freelancer', 'client')
            ->loadCount('proposals')
    ]);
}



    /**
 * Show all projects of the authenticated client
 */
public function myProjects()
{
    $user = Auth::user();

    // Fetch projects with client and count of proposals
    $projects = Project::with('client')
        ->withCount('proposals') // <-- adds proposals_count
        ->where('client_id', $user->id)
        ->latest()
        ->get();

    return response()->json([
        'data' => $projects
    ]);
}

/**
 * Update a project
 */
public function update(Request $request, Project $project)
{
    $user = Auth::user();

    // Only the project owner can edit
    if ($project->client_id !== $user->id) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    $data = $request->validate([
        'title' => 'sometimes|required|string|max:255',
        'description' => 'sometimes|required|string',
        'budget' => 'sometimes|required|numeric',
        'location' => 'nullable|string|max:255',
        'category_badge' => 'nullable|string|max:255',
        'status' => 'sometimes|in:open,in_progress,completed',
    ]);

    $project->update($data);

    return response()->json([
        'status' => true,
        'message' => 'Project updated successfully',
        'project' => $project->fresh()->load('client')
    ]);
}


/**
 * Delete a project
 */
public function destroy(Project $project)
{
    $user = Auth::user();

    // Only the project owner can delete
    if ($project->client_id !== $user->id) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    $project->delete();

    return response()->json([
        'status' => true,
        'message' => 'Project deleted successfully'
    ]);
}


}
