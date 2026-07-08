<?php

namespace App\Http\Controllers;

use App\Models\SupportMessage;
use Illuminate\Http\Request;

class SupportMessageController extends Controller
{
    // Fetch all messages for a specific user
    public function index($userId)
    {
        $messages = SupportMessage::where('registration_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();
            
        return response()->json([
            'status' => 'success',
            'messages' => $messages
        ]);
    }

    // User sends a message
    public function store(Request $request)
    {
        $request->validate([
            'registration_id' => 'required|exists:registrations,id',
            'message' => 'required|string',
        ]);

        $message = SupportMessage::create([
            'registration_id' => $request->registration_id,
            'message' => $request->message,
            'is_from_admin' => false,
        ]);

        // Generate automated bot reply
        $botReplyText = \App\Services\BotService::generateReply($request->message);

        // Save bot reply
        SupportMessage::create([
            'registration_id' => $request->registration_id,
            'message' => $botReplyText,
            'is_from_admin' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Message sent and bot replied',
            'data' => $message
        ]);
    }

    // Admin replies to a message (Simulated endpoint)
    public function adminReply(Request $request)
    {
        $request->validate([
            'registration_id' => 'required|exists:registrations,id',
            'message' => 'required|string',
        ]);

        $message = SupportMessage::create([
            'registration_id' => $request->registration_id,
            'message' => $request->message,
            'is_from_admin' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Admin replied',
            'data' => $message
        ]);
    }
}
