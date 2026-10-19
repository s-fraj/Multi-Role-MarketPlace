<x-guest-layout>
    <h1 class="font-display font-medium text-2xl text-teal-deep mb-1">Content de te revoir</h1>
    <p class="text-sm text-muted mb-7">Connecte-toi pour accéder à ton compte.</p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-line text-teal shadow-sm focus:ring-teal" name="remember">
                <span class="ms-2 text-sm text-muted">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-4">
            @if (Route::has('password.request'))
                <a class="text-sm text-teal hover:text-teal-deep focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal rounded-md" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <div class="flex items-center gap-3 my-6">
        <div class="flex-1 h-px bg-line"></div>
        <span class="text-xs text-muted">ou</span>
        <div class="flex-1 h-px bg-line"></div>
    </div>

    <button type="button" class="w-full flex items-center justify-center gap-2 py-2.5 rounded-lg border border-line bg-card text-sm text-ink hover:bg-paper transition">
        Continuer avec Google
    </button>
</x-guest-layout>
