@php
    $user = Auth::user();

    $nameParts = $user ? collect(preg_split('/\s+/', trim($user->name)))->filter() : collect();
    $initials = $nameParts->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
    $firstName = $user ? ($nameParts->first() ?? $user->name) : null;
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Souq — Marketplace' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,500;9..144,600&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="souq-app">

<header>
    <div class="header-left">
        <div class="menu-group">
            <button type="button" class="menu-btn"><i class="ti ti-menu-2"></i></button>
            <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="logo"><span class="logo-mark"><i class="ti ti-building-store"></i></span>Souk</a>

            <div class="categories-dropdown">
                @foreach ($categoriesMenu ?? [] as $cat)
                    <a href="{{ route('produits.index', ['categorie' => $cat]) }}">{{ $cat }}</a>
                @endforeach
            </div>
        </div>
    </div>

    <form action="{{ route('produits.index') }}" method="GET" class="header-search"
          x-data="produitSearch()" @click.outside="ouvert = false">
        <input type="text" name="recherche" placeholder="Rechercher un produit, une boutique..."
               value="{{ request('recherche') }}" autocomplete="off"
               x-model="terme"
               @input.debounce.200ms="rechercher()"
               @focus="if (suggestions.length) ouvert = true"
               @keydown.down.prevent="deplacerSelection(1)"
               @keydown.up.prevent="deplacerSelection(-1)"
               @keydown.enter="if (indexActif >= 0) { $event.preventDefault(); validerSelection(); }"
               @keydown.escape="ouvert = false" />
        <button type="submit"><i class="ti ti-search"></i></button>

        <div class="search-suggestions" x-show="ouvert" x-cloak x-transition.opacity.duration.120ms>
            <template x-if="chargement">
                <div class="search-suggestion-empty">Recherche...</div>
            </template>
            <template x-if="!chargement && suggestions.length === 0 && terme.trim().length > 0">
                <div class="search-suggestion-empty">Aucun résultat pour « <span x-text="terme"></span> »</div>
            </template>
            <template x-for="(s, i) in suggestions" :key="s.id">
                <a href="#" @click.prevent="rechercherTerme(s.nom)" class="search-suggestion-item" :class="{ 'active': i === indexActif }" @mouseenter="indexActif = i">
                    <i class="ti ti-search"></i>
                    <span class="search-suggestion-name" x-html="surligner(s.nom)"></span>
                    <span class="search-suggestion-meta" x-text="(s.boutique ? s.boutique + ' · ' : '') + s.prix + ' DT'"></span>
                </a>
            </template>
        </div>
    </form>

    <div class="header-actions">
        @auth
            @unless (Auth::user()->isAdmin())
                <div class="notif-wrap" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" class="icon-btn" @click="open = !open; if (open) { marquerNotificationsLues(); }">
                        <i class="ti ti-bell"></i>
                        <span class="badge" id="notif-badge" style="display:none;">0</span>
                    </button>

                    <div class="notif-dropdown" x-show="open" x-cloak x-transition.opacity.duration.120ms>
                        <div class="notif-dropdown-head">Notifications</div>
                        <div id="notif-list">
                            <div class="notif-empty">Aucune notification pour le moment.</div>
                        </div>
                        <a href="{{ route(Auth::user()->isVendeur() ? 'vendeur.notifications' : 'notifications.index') }}" class="notif-dropdown-footer">
                            Voir toutes les notifications →
                        </a>
                    </div>
                </div>
            @endunless

            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button type="button" class="account-link" style="border:none; background:none; cursor:pointer; font-family:inherit;">
                        <div class="avatar" data-avatar-display>
                            @if ($user->avatar)
                                <img src="{{ $user->avatarUrl() }}" alt="">
                            @else
                                <i class="ti ti-user"></i>     
                            @endif
                        </div>
                                
                        
                           
                        
                          
                        <span>
                            <small>Bonjour, {{ Str::limit(Auth::user()->name, 14) }}</small>
                            Mon compte
                        </span>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route(Auth::user()->profileRouteName())">
                        {{ __('Mon compte') }}
                    </x-dropdown-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Se déconnecter') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        @else
            <a href="{{ route('login') }}" class="account-link">
                <i class="ti ti-user"></i>
                <span>
                    <small>Bienvenue</small>
                    Se connecter / S'inscrire
                </span>
            </a>
        @endauth
        @if (auth()->check() && Auth::user()->isVendeur())
            <a href="{{ route('vendeur.produits.index') }}" class="cart-link">
                <span class="cart-icon-wrap">
                    <i class="ti ti-package"></i>
                    <span class="badge" id="cart-badge" style="{{ ( $produitsEnLigneCount ?? 0 ) > 0 ? '' : 'display:none;' }}">{{ $produitsEnLigneCount ?? 0 }}</span>

                </span>
                Produits
            </a>
        @else
            <a href="{{ route('panier.index') }}" class="cart-link">
                <span class="cart-icon-wrap">
                    <i class="ti ti-shopping-cart"></i>
                    <span class="badge" id="cart-badge" style="{{ ($panierCount ?? 0) > 0 ? '' : 'display:none;' }}">{{ $panierCount ?? 0 }}</span>
                </span>
                Panier
            </a>
        @endif
    </div>
</header>

{{ $slot }}

<footer>
    <span class="footer-logo">Souq</span>
    <span>© {{ date('Y') }} Souq — Marketplace. Tous droits réservés.</span>
</footer>

<div class="toast-container" id="toast-container"></div>

<script>
    // Global helper — usable from any page that extends this layout.
    // type is 'success' or 'error'.
    window.showToast = function (message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        const icon = type === 'success' ? 'ti-circle-check' : 'ti-alert-circle';
        toast.innerHTML = `<i class="ti ${icon}"></i><span>${message}</span>`;
        container.appendChild(toast);

        requestAnimationFrame(() => toast.classList.add('show'));

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 350);
        }, 3500);
    };

    window.updateCartBadge = function (count) {
        const badge = document.getElementById('cart-badge');
        if (! badge) return;
        badge.textContent = count;
        badge.style.display = count > 0 ? '' : 'none';
    };

    window.souqCsrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Shared add-to-cart call, used by the produits grid and the produit-detail page.
    // onSuccess(data) receives the JSON body when the product was added.
    window.ajouterAuPanier = function (produitId, quantite = 1, onSuccess = null) {
        fetch(`/panier/ajouter/${produitId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.souqCsrfToken,
            },
            credentials: 'same-origin',
            body: JSON.stringify({ quantite }),
        })
            .then(async (response) => {
                if (response.status === 401) {
                    window.location.href = '{{ route('login') }}';
                    return;
                }

                const texte = await response.text();
                let data = {};
                try { data = JSON.parse(texte); } catch (e) {
                    console.error('Réponse non-JSON du serveur :', texte);
                    showToast("Erreur serveur — voir la console (F12)", 'error');
                    return;
                }

                if (!response.ok || !data.success) {
                    showToast(data.message || "Vous n'avez pas cette option.", 'error');
                    return;
                }

                showToast(data.message || 'Produit ajouté au panier.', 'success');
                updateCartBadge(data.panierCount);
                if (typeof onSuccess === 'function') onSuccess(data);
            })
            .catch((err) => {
                console.error('Erreur réseau / fetch :', err);
                showToast("Une erreur est survenue, réessaie.", 'error');
            });
    };


        // Shared favorites toggle, used by the heart icon on product cards and
    // the produit-detail page. heartEl is the clicked element; toggles its
    // visual state (filled red) and calls onToggle(estFavori) if given.
    window.toggleFavori = function (produitId, heartEl, onToggle = null) {
        fetch(`/favoris/toggle/${produitId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.souqCsrfToken,
            },
        })
            .then(async (response) => {
                if (response.status === 401) {
                    window.location.href = '{{ route('login') }}';
                    return;
                }

                const data = await response.json();

                if (! response.ok || ! data.success) {
                    showToast(data.message || "Vous n'avez pas cette option.", 'error');
                    return;
                }

                if (heartEl) {
                    heartEl.classList.toggle('active', data.favori);
                }

                showToast(data.message, 'success');

                if (typeof onToggle === 'function') {
                    onToggle(data.favori);
                }
            })
            .catch(() => showToast("Une erreur est survenue, réessaie.", 'error'));
    };

   
    @if (session('toast_success'))
        showToast(@json(session('toast_success')), 'success');
    @endif
    @if (session('toast_error'))
        showToast(@json(session('toast_error')), 'error');
    @endif

    // Notification bell — polls /notifications/recentes every 15s so client
    // and vendeur see moderation decisions (produit approuvé/refusé, compte
    // bloqué...) without reloading the page. No websocket server required.
    @auth
        @unless (Auth::user()->isAdmin())
            (function () {
                let dernierMaxId = null;
                let premierChargement = true;

                function rendreNotifications(liste) {
                    const container = document.getElementById('notif-list');
                    if (! container) return;

                    if (! liste.length) {
                        container.innerHTML = '<div class="notif-empty">Aucune notification pour le moment.</div>';
                        return;
                    }

                    container.innerHTML = liste.map(function (n) {
                        const tag = n.lien ? 'a' : 'div';
                        const href = n.lien ? ` href="${n.lien}"` : '';
                        return `<${tag}${href} class="notif-item ${n.lu ? '' : 'unread'}" style="text-decoration:none; color:inherit; ${n.lien ? 'cursor:pointer;' : ''}">
                            <i class="ti ${n.icone}"></i>
                            <div>
                                <div class="notif-message">${n.message}</div>
                                <div class="notif-date">${n.date}</div>
                            </div>
                        </${tag}>`;
                    }).join('');
                }

                function chargerNotifications() {
                    fetch('{{ route('notifications.recentes') }}', { headers: { 'Accept': 'application/json' } })
                        .then((r) => (r.ok ? r.json() : null))
                        .then((data) => {
                            if (! data) return;

                            const badge = document.getElementById('notif-badge');
                            if (badge) {
                                badge.textContent = data.nonLues;
                                badge.style.display = data.nonLues > 0 ? '' : 'none';
                            }

                            rendreNotifications(data.notifications);

                            const ids = data.notifications.map((n) => n.id);
                            const maxId = ids.length ? Math.max(...ids) : 0;

                            if (! premierChargement && dernierMaxId !== null && maxId > dernierMaxId) {
                                const derniere = data.notifications.find((n) => n.id === maxId);
                                if (derniere) showToast(derniere.message, 'success');
                            }

                            dernierMaxId = maxId;
                            premierChargement = false;
                        })
                        .catch(() => {});
                }

                window.marquerNotificationsLues = function () {
                    fetch('{{ route('notifications.marquer-lues') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': window.souqCsrfToken,
                        },
                    })
                        .then(() => {
                            const badge = document.getElementById('notif-badge');
                            if (badge) badge.style.display = 'none';
                        })
                        .catch(() => {});
                };

                chargerNotifications();
                setInterval(chargerNotifications, 15000);
            })();
        @endunless
    @endauth
</script>

</body>
</html>
