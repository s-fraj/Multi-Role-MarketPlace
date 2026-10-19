<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Souq') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,500;9..144,600&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink antialiased bg-paper">

        <!-- Top bar -->
        <header class="flex items-center justify-between px-8 lg:px-12 py-5 border-b border-line bg-paper">
            <a href="/" class="font-display text-2xl font-semibold text-teal-deep">Souq</a>
            <a href="/" class="text-sm text-graphite hover:text-ink flex items-center gap-2">
                <span aria-hidden="true">&larr;</span> Retour à l'accueil
            </a>
        </header>

        <!-- Split panel -->
        <div class="grid lg:grid-cols-2 min-h-[calc(100vh-73px)]">

            <!-- Left: trust panel -->
            <div class="hidden lg:flex flex-col justify-center relative overflow-hidden bg-teal-deep text-paper px-16 py-12">
                <h2 class="font-display font-medium text-4xl leading-tight max-w-md mb-4">
                    Rejoins un marché fait de vraies boutiques et de vraies histoires.
                </h2>
                <p class="text-sm leading-relaxed max-w-sm mb-10" style="color: #B9C4C2;">
                    Que tu viennes acheter ou vendre, Souq te connecte à une communauté de vendeurs indépendants, en toute confiance.
                </p>
                <div class="flex gap-10">
                    <div>
                        <span class="block font-display text-2xl font-medium text-ochre">3 200+</span>
                        <span class="text-xs" style="color: #B9C4C2;">boutiques actives</span>
                    </div>
                    <div>
                        <span class="block font-display text-2xl font-medium text-ochre">98%</span>
                        <span class="text-xs" style="color: #B9C4C2;">commandes sans litige</span>
                    </div>
                    <div>
                        <span class="block font-display text-2xl font-medium text-ochre">24h</span>
                        <span class="text-xs" style="color: #B9C4C2;">support moyen</span>
                    </div>
                </div>

                <svg class="absolute -bottom-10 -right-10 opacity-40" width="220" height="220" viewBox="0 0 220 220" aria-hidden="true">
                    <circle cx="110" cy="110" r="100" fill="none" stroke="#E2A33B" stroke-width="1" />
                    <circle cx="110" cy="110" r="70" fill="none" stroke="#E2A33B" stroke-width="1" />
                    <circle cx="110" cy="110" r="40" fill="none" stroke="#E2A33B" stroke-width="1" />
                </svg>
            </div>

            <!-- Right: auth tabs + form -->
            <div class="flex flex-col justify-center px-8 lg:px-20 py-12 bg-card">
                <div class="w-full max-w-sm mx-auto lg:mx-0">

                    <div class="flex border border-line rounded-lg p-1 mb-8">
                        <a href="{{ route('login') }}"
                           class="flex-1 text-center text-sm font-medium py-2.5 rounded-md transition
                                  {{ request()->routeIs('login') ? 'bg-teal text-paper' : 'text-graphite hover:text-ink' }}">
                            Se connecter
                        </a>
                        <a href="{{ route('register') }}"
                           class="flex-1 text-center text-sm font-medium py-2.5 rounded-md transition
                                  {{ request()->routeIs('register') ? 'bg-teal text-paper' : 'text-graphite hover:text-ink' }}">
                            S'inscrire
                        </a>
                    </div>

                    {{ $slot }}

                </div>
            </div>
        </div>
    </body>
</html>
