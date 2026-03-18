<?php

/**
 * Propriété de ZAKARIAE BOUKHROUF - Projet HB Smart Clinic.
 * Tous droits d'utilisation réservés à ZAKARIAE BOUKHROUF pour le cabinet Healthcare Balance.
 */

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('role')->orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $user = new User();
        $roles = Role::orderBy('name')->get();

        return view('users.create', compact('user', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role_id' => ['required', 'exists:roles,id'],
            'is_active' => ['boolean'],
        ];
        if ($request->user()->hasRole('super_admin')) {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }
        $validated = $request->validate($rules);
        $validated['is_active'] = $request->boolean('is_active', true);
        if ($request->user()->hasRole('super_admin') && ! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            $validated['password'] = Hash::make(Str::random(32));
        }

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'Utilisateur créé.');
    }

    public function show(User $user): View
    {
        $user->load('role');

        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $roles = Role::orderBy('name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role_id' => ['required', 'exists:roles,id'],
            'is_active' => ['boolean'],
        ];
        if ($request->user()->hasRole('super_admin')) {
            $rules['password'] = ['nullable', 'confirmed', Password::defaults()];
        }
        $validated = $request->validate($rules);
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->user()->hasRole('super_admin') && ! empty($validated['password'] ?? null)) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé.');
    }
}
