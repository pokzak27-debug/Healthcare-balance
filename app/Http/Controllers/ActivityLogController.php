<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Liste des logs d'activité.
     */
    public function __invoke(Request $request): View
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if ($action = trim($request->get('action'))) {
            $query->where('action', 'like', "%{$action}%");
        }
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('activity-logs.index', compact('logs'));
    }
}
