<x-guest-layout>
    <h1 class="font-display font-medium text-2xl text-teal-deep mb-1">Nouveau mot de passe</h1>
    <p class="text-sm text-muted mb-7">Choisis un nouveau mot de passe pour ton compte {{ $request->email }}.</p>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address (kept, not shown — comes from the reset link) -->
        <input type="hidden" name="email" value="{{ old('email', $request->email) }}">
        <x-input-error :messages="$errors->get('email')" class="mb-4" />

        <!-- Nouveau mot de passe -->
        <div>
            <x-input-label for="password" value="Nouveau mot de passe" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autofocus autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmer le mot de passe -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirmer le mot de passe" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>
                Réinitialiser le mot de passe
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
