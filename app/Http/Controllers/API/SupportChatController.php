<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SupportChat;
use Illuminate\Http\Request;

class SupportChatController extends Controller
{
    public function index()
    {
        $chats = SupportChat::with(['user', 'support'])->get();
        return response()->json($chats);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'support_id' => 'required|exists:users,id',
            'status' => 'sometimes|string',
        ]);

        $chat = SupportChat::create($request->all());
        return response()->json($chat, 201);
    }

    public function show(SupportChat $supportChat)
    {
        return response()->json($supportChat->load(['user', 'support']));
    }

    public function update(Request $request, SupportChat $supportChat)
    {
        $request->validate([
            'status' => 'sometimes|string',
        ]);

        $supportChat->update($request->all());
        return response()->json($supportChat);
    }

    public function destroy(SupportChat $supportChat)
    {
        $supportChat->delete();
        return response()->json(null, 204);
    }

    public function sendMessage(Request $request, SupportChat $chat)
    {
        $request->validate([
            'message' => 'required|string',
            'is_support' => 'required|boolean',
        ]);

        $message = $chat->messages()->create([
            'user_id' => $request->is_support ? $chat->support_id : $chat->user_id,
            'message' => $request->message,
            'is_support' => $request->is_support,
        ]);

        return response()->json($message, 201);
    }
}
