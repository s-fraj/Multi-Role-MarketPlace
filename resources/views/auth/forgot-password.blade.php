<x-guest-layout>
    <h1 class="font-display font-medium text-2xl text-teal-deep mb-1">Mot de passe oublié</h1>
    <p class="text-sm text-muted mb-7">Indique ton email, on t'envoie un lien pour en choisir un nouveau.</p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="text-sm text-teal hover:text-teal-deep focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal rounded-md" href="{{ route('login') }}">
                &larr; Retour à la connexion
            </a>

            <x-primary-button>
                Envoyer le lien
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
