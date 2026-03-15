<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $query = auth()->user()->notifications();

        if ($request->filled('read')) {
            if ($request->get('read') === '1') {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }
        if ($type = $request->get('type')) {
            $query->whereJsonContains('data->type', $type);
        }

        $notifications = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        $url = $notification->data['url'] ?? route('notifications.index');

        return redirect($url);
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications->count(),
        ]);
    }

    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }
}
