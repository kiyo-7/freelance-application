<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * List all projects with client info
     */
    public function index()
    {
        return Project::with('client')->latest()->get();
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
        $data['client_name'] = $user->name ?? 'Client'; // fallback if name missing

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
}
