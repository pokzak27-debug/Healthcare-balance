<?php

namespace App\Http\Controllers;

use App\Models\Pole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PoleController extends Controller
{
    public function index(): View
    {
        $poles = Pole::ordered()->get();

        return view('poles.index', compact('poles'));
    }

    public function create(): View
    {
        $pole = new Pole();

        return view('poles.create', compact('pole'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] ??= 0;

        Pole::create($validated);

        return redirect()->route('poles.index')->with('success', 'Pôle créé.');
    }

    public function show(Pole $pole): View
    {
        $pole->loadCount('appointments');

        return view('poles.show', compact('pole'));
    }

    public function edit(Pole $pole): View
    {
        return view('poles.edit', compact('pole'));
    }

    public function update(Request $request, Pole $pole): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] ??= $pole->sort_order;

        $pole->update($validated);

        return redirect()->route('poles.index')->with('success', 'Pôle mis à jour.');
    }

    public function destroy(Pole $pole): RedirectResponse
    {
        if ($pole->appointments()->exists()) {
            return redirect()->route('poles.index')
                ->with('error', 'Impossible de supprimer ce pôle : des rendez-vous y sont rattachés.');
        }
        $pole->delete();

        return redirect()->route('poles.index')->with('success', 'Pôle supprimé.');
    }
}
