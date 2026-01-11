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
        return $project->load('applications.freelancer', 'client');
    }

    /**
     * Show all projects of the authenticated client
     */
    public function myProjects()
{
    $user = Auth::user();

    $projects = Project::with('client')
        ->where('client_id', $user->id)
        ->latest()
        ->get();

    return response()->json([
        'data' => $projects
    ]);
}
}
