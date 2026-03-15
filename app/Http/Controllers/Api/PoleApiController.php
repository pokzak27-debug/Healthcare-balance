<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PoleApiController extends Controller
{
    /**
     * Liste des pôles.
     */
    public function index(): JsonResponse
    {
        $poles = Pole::active()->ordered()->get();

        return response()->json($poles);
    }

    /**
     * Affiche un pôle.
     */
    public function show(Pole $pole): JsonResponse
    {
        $pole->loadCount('appointments');

        return response()->json($pole);
    }

    /**
     * Crée un pôle (réservé super_admin).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $pole = Pole::create($validated);

        return response()->json($pole, 201);
    }

    /**
     * Met à jour un pôle (réservé super_admin).
     */
    public function update(Request $request, Pole $pole): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
        if (isset($validated['name'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        $pole->update($validated);

        return response()->json($pole);
    }

    /**
     * Supprime un pôle (réservé super_admin).
     */
    public function destroy(Pole $pole): JsonResponse
    {
        if ($pole->appointments()->exists()) {
            return response()->json(['message' => 'Impossible de supprimer : des rendez-vous y sont rattachés.'], 422);
        }
        $pole->delete();

        return response()->json(null, 204);
    }
}
