<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /** Liste des messages (JSON pour le chat). */
    public function index(): JsonResponse
    {
        $messages = ChatMessage::with('user')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'body' => $m->body,
                'user_name' => $m->user?->name ?? '—',
                'user_id' => $m->user_id,
                'is_me' => $m->user_id === Auth::id(),
                'created_at' => $m->created_at->format('d/m H:i'),
                'created_at_iso' => $m->created_at->toIso8601String(),
            ]);

        return response()->json(['messages' => $messages]);
    }

    /** Envoyer un message. */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $msg = ChatMessage::create([
            'user_id' => Auth::id(),
            'body' => $validated['body'],
        ]);
        $msg->load('user');

        return response()->json([
            'message' => [
                'id' => $msg->id,
                'body' => $msg->body,
                'user_name' => $msg->user?->name ?? '—',
                'user_id' => $msg->user_id,
                'is_me' => true,
                'created_at' => $msg->created_at->format('d/m H:i'),
                'created_at_iso' => $msg->created_at->toIso8601String(),
            ],
        ], 201);
    }
}
