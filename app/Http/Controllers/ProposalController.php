<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProposalController extends Controller
{
    /**
     * Submit proposal (Freelancer)
     */
    public function store(Request $request, $projectId)
    {
        $user = Auth::user();

        if ($user->role !== 'freelancer') {
            return response()->json([
                'message' => 'Only freelancers can submit proposals.'
            ], 403);
        }

        $project = Project::where('status', 'open')->findOrFail($projectId);

        // Prevent duplicate proposals
        if (
            Proposal::where('project_id', $project->id)
                ->where('freelancer_id', $user->id)
                ->exists()
        ) {
            return response()->json([
                'message' => 'Proposal already submitted.'
            ], 422);
        }

        $validated = $request->validate([
            'bid_amount'    => 'required|numeric|min:1',
            'delivery_time' => 'required|integer|min:1',
            'cover_letter'  => 'required|string|min:20',
        ]);

        $proposal = Proposal::create([
            'project_id'    => $project->id,
            'freelancer_id' => $user->id,
            'bid_amount'    => $validated['bid_amount'],
            'delivery_time' => $validated['delivery_time'],
            'cover_letter'  => $validated['cover_letter'],
            'status'        => 'pending',
        ]);

        // 🔔 Notify client about new proposal
        Notification::create([
            'user_id' => $project->client_id,
            'title'   => 'New Proposal Received',
            'message' => "{$user->name} has submitted a proposal for your project \"{$project->title}\".",
            'type'    => 'job',
        ]);

        return response()->json([
            'message' => 'Proposal submitted successfully.',
            'data'    => $proposal,
        ], 201);
    }

    /**
     * List proposals (Client)
     */
    public function index($projectId)
    {
        $project = Project::with('proposals.freelancer.freelancerProfile')
            ->findOrFail($projectId);

        if ($project->client_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized access.'
            ], 403);
        }

        return response()->json([
            'data' => $project->proposals()
                ->with('freelancer.freelancerProfile')
                ->latest()
                ->get(),
        ]);
    }

    /**
     * Accept proposal (Client)
     */
    public function accept($proposalId)
    {
        $proposal = Proposal::with('project')->findOrFail($proposalId);
        $project  = $proposal->project;

        if ($project->client_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        if ($project->freelancer_id) {
            return response()->json([
                'message' => 'Project already assigned.'
            ], 422);
        }

        DB::transaction(function () use ($proposal, $project) {

            // Accept selected proposal
            $proposal->update([
                'status' => 'accepted'
            ]);

            // ❌ Reject other proposals
        $rejectedProposals = Proposal::where('project_id', $project->id)
            ->where('id', '!=', $proposal->id)
            ->get();

        foreach ($rejectedProposals as $rejected) {
            $rejected->update(['status' => 'rejected']);

            // 🔔 Notify rejected freelancers
            Notification::create([
                'user_id' => $rejected->freelancer_id,
                'title'   => 'Proposal Rejected',
                'message' => "Your proposal for \"{$project->title}\" was not selected.",
                'type'    => 'job',
            ]);
        }
            // Assign freelancer to project
            $project->update([
                'freelancer_id' => $proposal->freelancer_id,
                'status'        => 'in_progress',
            ]);

            // 🔔 Notify accepted freelancer
            Notification::create([
                'user_id' => $proposal->freelancer_id,
                'title'   => 'Proposal Accepted 🎉',
                'message' => "Congratulations! You have been selected for the project \"{$project->title}\".",
                'type'    => 'job',
            ]);
        });

        return response()->json([
            'message' => 'Proposal accepted and freelancer assigned.',
            'data'    => $proposal,
        ]);
    }

        /**
     * Get accepted projects for authenticated freelancer
     */
    public function acceptedProjects()
    {
        $user = Auth::user();

        if ($user->role !== 'freelancer') {
            return response()->json([
                'message' => 'Only freelancers can access this resource.'
            ], 403);
        }

        $projects = Project::with([
                'client',
                'proposals' => function ($query) use ($user) {
                    $query->where('freelancer_id', $user->id)
                        ->where('status', 'accepted');
                }
            ])
            ->where('freelancer_id', $user->id)
            ->where('status', 'in_progress')
            ->latest()
            ->get();

        return response()->json([
            'data' => $projects
        ]);
    }

    /**
     * Reject proposal (Client)
     */
    public function reject($proposalId)
    {
        $proposal = Proposal::with('project')->findOrFail($proposalId);

        if ($proposal->project->client_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        if ($proposal->status !== 'pending') {
            return response()->json([
                'message' => 'Proposal already processed.'
            ], 422);
        }

        $proposal->update([
            'status' => 'rejected'
        ]);

        Notification::create([
        'user_id' => $proposal->freelancer_id,
        'title'   => 'Proposal Rejected',
        'message' => "Your proposal for \"{$proposal->project->title}\" was rejected by the client.",
        'type'    => 'job',
    ]);

        // ❌ Delete proposal
        $proposal->delete();
        return response()->json([
            'message' => 'Proposal rejected and freelancer notified.'
        ]);
    }
}
