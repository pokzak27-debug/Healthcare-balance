<div class="space-y-4">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Nom *</label>
        <input type="text" name="name" id="name" value="{{ old('name', $user->name ?? '') }}" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
        <input type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password" class="block text-sm font-medium text-gray-700">{{ isset($user) && $user->exists ? 'Nouveau mot de passe (laisser vide pour ne pas changer)' : 'Mot de passe *' }}</label>
        <input type="password" name="password" id="password"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('password') border-red-500 @enderror"
            {{ !isset($user) || !$user->exists ? 'required' : '' }}>
        @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    @if(!isset($user) || !$user->exists)
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmer le mot de passe *</label>
        <input type="password" name="password_confirmation" id="password_confirmation" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    @else
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmer (si changement)</label>
        <input type="password" name="password_confirmation" id="password_confirmation"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    @endif
    <div>
        <label for="role_id" class="block text-sm font-medium text-gray-700">Rôle *</label>
        <select name="role_id" id="role_id" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('role_id') border-red-500 @enderror">
            <option value="">— Choisir —</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}" {{ old('role_id', $user->role_id ?? '') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
            @endforeach
        </select>
        @error('role_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm text-gray-700">Actif</span>
        </label>
    </div>
</div>
