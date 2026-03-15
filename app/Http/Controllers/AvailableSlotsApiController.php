<?php

namespace App\Http\Controllers;

use App\Services\AvailableSlotsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailableSlotsApiController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'pole_id' => ['required', 'exists:poles,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'duration_minutes' => ['nullable', 'integer', 'in:15,30,45,60,90'],
        ]);

        $service = app(AvailableSlotsService::class);
        $times = $service->getAvailableTimes(
            $validated['date'],
            (int) $validated['pole_id'],
            $validated['user_id'] ? (int) $validated['user_id'] : null,
            (int) ($validated['duration_minutes'] ?? 30)
        );

        return response()->json(['times' => $times]);
    }
}
