<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChatController extends Controller
{
    // Get all conversations for the logged-in user
    public function index()
    {
        $user = Auth::user();

        // Fetch conversations where user is client or freelancer
        $conversations = Conversation::with(['freelancer', 'client'])
            ->where('client_id', $user->id)
            ->orWhere('freelancer_id', $user->id)
            ->orderByDesc('last_message_time')
            ->get();

        // Map to Flutter JSON structure
        $result = $conversations->map(function ($conv) use ($user) {
            $freelancer = $conv->freelancer_id == $user->id ? $conv->client : $conv->freelancer;

            return [
                'id' => (string)$conv->id,
                'freelancer' => [
                    'id' => (string)$freelancer->id,
                    'name' => $freelancer->name ?? 'Unknown',
                    'avatarUrl' => optional($freelancer->profile)->avatar_url,
                    'isOnline' => false, // optional: implement realtime online check
                    'lastSeen' => null,
                ],
                'lastMessage' => $conv->last_message ?? '',
                'lastMessageTime' => $conv->last_message_time?->toIso8601String() ?? now()->toIso8601String(),
                'unreadCount' => $conv->client_id == $user->id ? $conv->client_unread : $conv->freelancer_unread,
                'isOnline' => false,
            ];
        });

        return response()->json($result);
    }

    // Get all messages in a conversation
    public function messages(Conversation $conversation)
    {
        $user = Auth::user();

        // Check if user is part of the conversation
        if (!in_array($user->id, [$conversation->client_id, $conversation->freelancer_id])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()->orderBy('timestamp')->get();

        $result = $messages->map(function ($msg) {
            return [
                'id' => (string)$msg->id,
                'conversationId' => (string)$msg->conversation_id,
                'senderId' => (string)$msg->sender_id,
                'senderRole' => $msg->sender_role,
                'content' => $msg->content,
                'timestamp' => $msg->timestamp->toIso8601String(),
                'status' => $msg->status,
                'type' => $msg->type,
                'fileUrl' => $msg->file_url,
                'fileName' => $msg->file_name,
                'fileSize' => $msg->file_size,
                'metadata' => $msg->metadata,
            ];
        });

        // Reset unread count for the current user
        if ($conversation->client_id == $user->id) {
            $conversation->client_unread = 0;
        } else {
            $conversation->freelancer_unread = 0;
        }
        $conversation->save();

        return response()->json($result);
    }

    // Send a new message
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $user = Auth::user();

        if (!in_array($user->id, [$conversation->client_id, $conversation->freelancer_id])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'content' => 'nullable|string',
            'type' => 'required|in:text,image,file',
            'file_url' => 'nullable|string',
            'file_name' => 'nullable|string',
            'file_size' => 'nullable|integer',
        ]);

        $senderRole = $conversation->client_id == $user->id ? 'client' : 'freelancer';

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'sender_role' => $senderRole,
            'type' => $data['type'],
            'content' => $data['content'] ?? '',
            'file_url' => $data['file_url'] ?? null,
            'file_name' => $data['file_name'] ?? null,
            'file_size' => $data['file_size'] ?? null,
            'status' => 'sent',
            'timestamp' => now(),
        ]);

        // Update last message & unread counters
        $conversation->last_message = $message->content ?: ($message->file_name ?? 'Attachment');
        $conversation->last_message_time = now();
        if ($senderRole === 'client') {
            $conversation->freelancer_unread += 1;
        } else {
            $conversation->client_unread += 1;
        }
        $conversation->save();

        return response()->json([
            'id' => (string)$message->id,
            'conversationId' => (string)$conversation->id,
            'senderId' => (string)$message->sender_id,
            'senderRole' => $message->sender_role,
            'content' => $message->content,
            'timestamp' => $message->timestamp->toIso8601String(),
            'status' => $message->status,
            'type' => $message->type,
            'fileUrl' => $message->file_url,
            'fileName' => $message->file_name,
            'fileSize' => $message->file_size,
            'metadata' => $message->metadata,
        ]);
    }
}
