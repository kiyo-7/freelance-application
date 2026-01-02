<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * List all reviews for a project
     */
    public function index(Project $project)
    {
        $reviews = Review::where('project_id', $project->id)
            ->with(['reviewer', 'reviewee'])
            ->latest()
            ->get();

        return response()->json($reviews);
    }

    /**
     * Store a new review for a project
     */
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'reviewee_id' => 'required|exists:authusers,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Prevent user from reviewing themselves
        if ($data['reviewee_id'] == Auth::id()) {
            return response()->json(['message' => 'You cannot review yourself.'], 400);
        }

        $review = Review::create([
            'project_id' => $project->id,
            'reviewer_id' => Auth::id(),
            'reviewee_id' => $data['reviewee_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return response()->json([
            'message' => 'Review created successfully',
            'review' => $review
        ], 201);
    }

    /**
     * Show a single review
     */
    public function show(Review $review)
    {
        return $review->load(['reviewer', 'reviewee', 'project']);
    }
}
