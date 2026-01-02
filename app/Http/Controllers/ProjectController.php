<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        return Project::with('client')->latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'budget' => 'required|numeric',
        ]);

        $data['client_id'] = Auth::id(); // ✅ correct way

        return Project::create($data);
    }

    public function show(Project $project)
    {
        return $project->load('applications.freelancer');
    }
}
