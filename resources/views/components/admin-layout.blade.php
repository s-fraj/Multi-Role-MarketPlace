<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Administration — Souq' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,500;9..144,600&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app">

@php
    $enAttenteCount = \App\Models\Produit::enAttente()->count();
@endphp

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-logo"><span class="logo-mark"><i class="ti ti-building-store"></i></span> Souq <span class="admin-tag">Admin</span></div>

        <nav class="admin-nav">
            <a href="{{ route('admin.dashboard') }}" @class(['active' => ($active ?? null) === 'dashboard'])><i class="ti ti-layout-dashboard"></i> Tableau de bord</a>
            <a href="{{ route('admin.utilisateurs') }}" @class(['active' => ($active ?? null) === 'utilisateurs'])><i class="ti ti-users"></i> Utilisateurs</a>
            <a href="{{ route('admin.produits') }}" @class(['active' => ($active ?? null) === 'produits'])><i class="ti ti-package"></i> Produits</a>
            <a href="{{ route('admin.produits.en-attente') }}" @class(['active' => ($active ?? null) === 'produits-en-attente'])>
                <i class="ti ti-clock-exclamation"></i> Produits en attente
                <span class="admin-nav-badge" id="badge-produits-en-attente" style="@if($enAttenteCount <= 0) display:none; @endif">{{ $enAttenteCount }}</span>
            </a>
            <a href="{{ route('admin.categories') }}" @class(['active' => ($active ?? null) === 'categories'])><i class="ti ti-category"></i> Catégories</a>
            <a href="{{ route('admin.commandes') }}" @class(['active' => ($active ?? null) === 'commandes'])><i class="ti ti-shopping-bag"></i> Commandes</a>
            <a href="{{ route('admin.commandes.drones') }}" @class(['active' => ($active ?? null) === 'commandes-drones'])><i class="ti ti-drone"></i> Suivre commandes (drones)</a>
        </nav>

        <div class="admin-sidebar-footer">
            <div class="avatar-mini">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</div>
            <div>
                <div class="name">{{ Auth::user()->name }}</div>
                <div class="role">Administrateur</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn" title="Se déconnecter"><i class="ti ti-logout"></i> Déconnexion</button>
            </form>
        </div>
    </aside>

    <main class="admin-main">
        {{ $slot }}
    </main>
</div>

<div class="toast-container" id="toast-container"></div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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

    window.decrementerBadge = function (id, montant = 1) {
        const badge = document.getElementById(id);
        if (! badge) return;
        const valeur = Math.max(0, parseInt(badge.textContent, 10) - montant);
        badge.textContent = valeur;
        badge.style.display = valeur > 0 ? '' : 'none';
    };

    window.envoyerAdmin = function (url, method, payload = {}) {
        return fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(payload),
        }).then(async (response) => {
            const data = await response.json().catch(() => ({}));
            if (! response.ok) {
                const premierMessage = data.errors ? Object.values(data.errors)[0][0] : (data.message || "Une erreur est survenue.");
                throw new Error(premierMessage);
            }
            return data;
        });
    };
</script>

</body>
</html>
