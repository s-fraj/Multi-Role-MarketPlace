<x-guest-layout>
    <h1 class="font-display font-medium text-2xl text-teal-deep mb-1">Crée ton compte</h1>
    <p class="text-sm text-muted mb-6">Rejoins Souq pour acheter ou vendre.</p>

    <form method="POST" action="{{ route('register') }}" x-data="{ role: '{{ old('role', 'client') }}' }">
        @csrf

        <!-- Type de compte -->
        <div class="mb-6">
            <x-input-label :value="__('Type de compte')" class="mb-2" />
            <div class="grid grid-cols-2 gap-3">
                <label class="border rounded-lg p-4 text-center cursor-pointer transition"
                       :class="role === 'client' ? 'border-teal bg-[#EEF5F4]' : 'border-line'">
                    <input type="radio" name="role" value="client" x-model="role" class="sr-only">
                    <i class="ti ti-shopping-bag text-xl text-teal block mb-1"></i>
                    <span class="text-sm font-medium">Je suis client</span>
                </label>

                <label class="border rounded-lg p-4 text-center cursor-pointer transition"
                       :class="role === 'vendeur' ? 'border-teal bg-[#EEF5F4]' : 'border-line'">
                    <input type="radio" name="role" value="vendeur" x-model="role" class="sr-only">
                    <i class="ti ti-building-store text-xl text-teal block mb-1"></i>
                    <span class="text-sm font-medium">Je suis vendeur</span>
                </label>
            </div>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Informations personnelles -->
        <h2 class="text-xs font-semibold uppercase tracking-wide text-muted mb-3">Informations personnelles</h2>

        <div>
            <x-input-label for="name" :value="__('Nom complet')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="grid grid-cols-2 gap-3 mt-4">
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="telephone" :value="__('Téléphone')" />
                <x-text-input id="telephone" class="block mt-1 w-full" type="tel" name="telephone" :value="old('telephone')" required autocomplete="tel" placeholder="20 000 000" />
                <x-input-error :messages="$errors->get('telephone')" class="mt-2" />
            </div>
        </div>

        <!-- Adresse -->
        <h2 class="text-xs font-semibold uppercase tracking-wide text-muted mt-6 mb-3">
            <span x-text="role === 'vendeur' ? 'Adresse de la boutique' : 'Adresse de livraison'"></span>
        </h2>

        <div>
            <x-input-label for="adresse" :value="__('Adresse')" />
            <x-text-input id="adresse" class="block mt-1 w-full" type="text" name="adresse" :value="old('adresse')" required placeholder="Rue, numéro, quartier" />
            <x-input-error :messages="$errors->get('adresse')" class="mt-2" />
        </div>

        <div class="grid grid-cols-2 gap-3 mt-4">
            <div>
                <x-input-label for="ville" :value="__('Ville')" />
                <x-text-input id="ville" class="block mt-1 w-full" type="text" name="ville" :value="old('ville')" required placeholder="Tunis" />
                <x-input-error :messages="$errors->get('ville')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="code_postal" :value="__('Code postal')" />
                <x-text-input id="code_postal" class="block mt-1 w-full" type="text" name="code_postal" :value="old('code_postal')" required maxlength="4" placeholder="1000" />
                <x-input-error :messages="$errors->get('code_postal')" class="mt-2" />
            </div>
        </div>

        <!-- Vendor-only: boutique -->
        <div x-show="role === 'vendeur'" x-cloak class="mt-6">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-muted mb-3">Ta boutique</h2>

            <div>
                <x-input-label for="nom_boutique" :value="__('Nom de la boutique')" />
                <x-text-input id="nom_boutique" class="block mt-1 w-full" type="text" name="nom_boutique" :value="old('nom_boutique')" placeholder="Ex: Atelier Amira" />
                <x-input-error :messages="$errors->get('nom_boutique')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="categorie_principale" :value="__('Catégorie principale')" />
                <select id="categorie_principale" name="categorie_principale" class="bg-paper border-line text-ink focus:border-teal focus:ring-teal rounded-lg shadow-sm text-sm block mt-1 w-full">
                    <option value="mode-textile">Mode &amp; Textile</option>
                    <option value="maison-artisanat">Maison &amp; Artisanat</option>
                    <option value="electronique">Électronique</option>
                    <option value="beaute">Beauté</option>
                    <option value="epicerie-fine">Épicerie fine</option>
                    <option value="enfants">Enfants</option>
                </select>
                <p class="text-xs text-muted mt-1">Tu pourras compléter les autres détails de ta boutique après l'inscription.</p>
            </div>
        </div>

        <!-- Sécurité -->
        <h2 class="text-xs font-semibold uppercase tracking-wide text-muted mt-6 mb-3">Sécurité</h2>

        <div>
            <x-input-label for="password" :value="__('Mot de passe')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <p class="text-xs text-muted mt-4 leading-relaxed">
            En créant ton compte, tu acceptes nos <a href="#" class="text-teal underline">conditions d'utilisation</a> et notre <a href="#" class="text-teal underline">politique de confidentialité</a>.
        </p>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center">
                {{ __('Créer mon compte') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
