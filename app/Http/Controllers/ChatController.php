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
            $conversations = Conversation::with(['freelancer', 'client'])->where(function ($q) use ($user) {
                $q->where('client_id', $user->id)
                ->orWhere('freelancer_id', $user->id);
            })->orderByDesc('last_message_time')->get();

            // Map to Flutter JSON structure
            $result = $conversations->map(function ($conv) use ($user) {
                // Determine the peer (the other user)
                $peer = $conv->client_id == $user->id ? $conv->freelancer : $conv->client;

                // Get peer profile depending on role
                $peerProfile = null;
                if ($peer->role === 'client') {
                    $peerProfile = $peer->clientProfile;
                } elseif ($peer->role === 'freelancer') {
                    $peerProfile = $peer->freelancerProfile;
                }

                return [
                    'id' => (string)$conv->id,
                    'peer' => [
                        'id' => (string)$peer->id,
                        'name' => $peerProfile->full_name ?? $peer->name ?? 'Unknown',
                        'avatarUrl' => $peerProfile->avatar_url ?? null,
                        'isOnline' => true,
                        'lastSeen' => null,
                    ],
                    'lastMessage' => $conv->last_message ?? '',
                    'lastMessageTime' => $conv->last_message_time?->toIso8601String() ?? now()->toIso8601String(),
                    'unreadCount' => $conv->client_id == $user->id ? $conv->client_unread : $conv->freelancer_unread,
                ];
            });

    return response()->json($result);
}

    // Get all messages in a conversation
    public function messages(Conversation $conversation)
{
        $user = Auth::user();

        if (!in_array($user->id, [$conversation->client_id, $conversation->freelancer_id])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()->orderBy('timestamp')->get();

        
        // Determine the peer
        $peer = $conversation->freelancer_id == $user->id ? $conversation->client : $conversation->freelancer;

                    $peerProfile = null;
            if ($peer->role === 'client') {
                $peerProfile = $peer->clientProfile;
            } elseif ($peer->role === 'freelancer') {
                $peerProfile = $peer->freelancerProfile;
            }

            $peerName = $peerProfile->full_name ?? 'Unknown';
            $peerAvatar = $peerProfile->avatar_url ?? null;

        $result = [
        'peer' => [
            'id' => (string)$peer->id,
            'name' => $peerName,          // <-- use the profile name
            'avatarUrl' => $peerAvatar,   // <-- use the profile avatar
            'isOnline' => true,
            'lastSeen' => null,
        ],
        'messages' => $messages->map(function ($msg) {
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
        }),
    ];
        // Reset unread count
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
    // Create a new conversation between a client and freelancer
public function createConversation(Request $request)
{
    $user = Auth::user();

    $data = $request->validate([
        'freelancer_id' => 'required|exists:users,id',
    ]);

    $freelancerId = $data['freelancer_id'];

    // Prevent user from chatting with themselves
    if ($freelancerId == $user->id) {
        return response()->json(['message' => 'Cannot create conversation with yourself'], 400);
    }

    // Check if conversation already exists
    $conversation = Conversation::where(function($q) use ($user, $freelancerId) {
        $q->where('client_id', $user->id)
          ->where('freelancer_id', $freelancerId);
    })->orWhere(function($q) use ($user, $freelancerId) {
        $q->where('client_id', $freelancerId)
          ->where('freelancer_id', $user->id);
    })->first();

    if (!$conversation) {
        // Create new conversation
        $conversation = Conversation::create([
            'client_id' => $user->id,
            'freelancer_id' => $freelancerId,
            'last_message' => null,
            'last_message_time' => now(),
            'client_unread' => 0,
            'freelancer_unread' => 0,
        ]);
    }

        return response()->json([
        'conversationId' => (string)$conversation->id, // add this line
        'client_id' => (string)$conversation->client_id,
        'freelancer_id' => (string)$conversation->freelancer_id,
        'lastMessage' => $conversation->last_message,
        'lastMessageTime' => $conversation->last_message_time?->toIso8601String() ?? now()->toIso8601String(),
        'clientUnread' => $conversation->client_unread,
        'freelancerUnread' => $conversation->freelancer_unread,
    ]);
}
}
