<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(Project $project)
    {
        return Message::where('project_id', $project->id)
            ->with(['sender', 'receiver'])
            ->latest()
            ->get();
    }

    // Send a message
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'receiver_id' => 'required|exists:authusers,id',
            'message' => 'required|string',
        ]);

        $message = Message::create([
            'project_id' => $project->id,
            'sender_id' => Auth::id(),
            'receiver_id' => $data['receiver_id'],
            'message' => $data['message'],
        ]);

        return response()->json([
            'message' => 'Message sent',
            'data' => $message
        ], 201);
    }

    public function show(Message $message)
    {
        return $message->load(['sender', 'receiver', 'project']);
    }
}
?>