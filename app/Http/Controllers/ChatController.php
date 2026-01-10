<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /* ==================================================
       GET /conversations
       List all conversations (chat list)
    ================================================== */
    public function index()
    {
        $user = Auth::user();

        $conversations = Conversation::with([
                'client.freelancerProfile',
                'freelancer.freelancerProfile'
            ])
            ->where('client_id', $user->id)
            ->orWhere('freelancer_id', $user->id)
            ->orderByDesc('last_message_time')
            ->get();

        $result = $conversations->map(function ($conv) use ($user) {

            $otherUser = $conv->client_id === $user->id
                ? $conv->freelancer
                : $conv->client;

            return [
                'id' => (string) $conv->id,
                'user' => [
                    'id' => (string) $otherUser->id,
                    'name' => $otherUser->freelancerProfile?->full_name ?? 'Unknown',
                    'avatarUrl' => $otherUser->freelancerProfile?->avatar_url,
                    'role' => $otherUser->role,
                ],
                'lastMessage' => $conv->last_message ?? '',
                'lastMessageTime' => $conv->last_message_time?->toIso8601String(),
                'unreadCount' => $conv->client_id === $user->id
                    ? $conv->client_unread
                    : $conv->freelancer_unread,
            ];
        });

        return response()->json($result);
    }

    /* ==================================================
       POST /conversations/{freelancer}
       Create or get a conversation
    ================================================== */
    public function create($freelancerId)
    {
        $user = Auth::user();

        if ($user->role !== 'client') {
            return response()->json(['message' => 'Only clients can start conversations'], 403);
        }

        $freelancer = User::where('id', $freelancerId)
            ->where('role', 'freelancer')
            ->firstOrFail();

        $conversation = Conversation::firstOrCreate([
            'client_id' => $user->id,
            'freelancer_id' => $freelancer->id,
        ]);

        return response()->json($conversation, 201);
    }

    /* ==================================================
       DELETE /conversations/{conversation}
       Delete a conversation
    ================================================== */
    public function destroy(Conversation $conversation)
    {
        $user = Auth::user();

        if (!in_array($user->id, [$conversation->client_id, $conversation->freelancer_id])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $conversation->delete();

        return response()->json(['message' => 'Conversation deleted']);
    }

    /* ==================================================
       GET /conversations/{conversation}/messages
       Get all messages
    ================================================== */
    public function messages(Conversation $conversation)
    {
        $user = Auth::user();

        if (!in_array($user->id, [$conversation->client_id, $conversation->freelancer_id])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()->orderBy('timestamp')->get();

        // Reset unread counter
        if ($conversation->client_id === $user->id) {
            $conversation->client_unread = 0;
        } else {
            $conversation->freelancer_unread = 0;
        }
        $conversation->save();

        return response()->json(
            $messages->map(fn ($msg) => [
                'id' => (string) $msg->id,
                'conversationId' => (string) $msg->conversation_id,
                'senderId' => (string) $msg->sender_id,
                'senderRole' => $msg->sender_role,
                'content' => $msg->content,
                'timestamp' => $msg->timestamp->toIso8601String(),
                'status' => $msg->status,
                'type' => $msg->type,
                'fileUrl' => $msg->file_url,
                'fileName' => $msg->file_name,
                'fileSize' => $msg->file_size,
                'metadata' => $msg->metadata,
            ])
        );
    }

    /* ==================================================
       POST /conversations/{conversation}/messages
       Send a message
    ================================================== */
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

        $senderRole = $conversation->client_id === $user->id
            ? 'client'
            : 'freelancer';

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

        // Update conversation preview + unread
        $conversation->last_message = $message->content ?: ($message->file_name ?? 'Attachment');
        $conversation->last_message_time = now();

        if ($senderRole === 'client') {
            $conversation->freelancer_unread++;
        } else {
            $conversation->client_unread++;
        }

        $conversation->save();

        return response()->json([
            'id' => (string) $message->id,
            'conversationId' => (string) $conversation->id,
            'senderId' => (string) $message->sender_id,
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
