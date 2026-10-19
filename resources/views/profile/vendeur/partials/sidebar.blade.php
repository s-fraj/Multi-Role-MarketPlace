@php
    $vendeurNotifNonLues = Auth::user()->notificationsVendeur()->where('lu', false)->count();
    $sidebarUser = Auth::user();
    $sidebarParts = collect(preg_split('/\s+/', trim($sidebarUser->name)))->filter();
    $sidebarInitials = $sidebarParts->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
    $sidebarNotifNonLues = $sidebarUser->notificationsClient()->where('lu', false)->count();
@endphp

    
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
<aside class="sidebar">
    <div class="account-card">
        <div class="avatar-wrap">
            <div class="avatar" data-avatar-display>
                @if ($sidebarUser->avatar)
                    <img src="{{ $sidebarUser->avatarUrl() }}" alt="">
                @else
                    {{ $sidebarInitials }}
                @endif
            </div>
            <label class="avatar-edit-btn" title="Changer la photo">
                <i class="ti ti-pencil"></i>
                <input type="file" id="sidebar-avatar-input" accept="image/png, image/jpeg, image/webp" hidden>
            </label>
        </div>
        <div class="account-name">{{ $sidebarUser->name }}</div>
        <div class="account-email">{{ $sidebarUser->email }}</div>
    </div>

    <a href="{{ route('vendeur.tableau-de-bord') }}" class="nav-item @if(($active ?? null) === 'tableau-de-bord') active @endif"><i class="ti ti-layout-dashboard"></i> Tableau de bord</a>
    <a href="{{ route('vendeur.boutique') }}" class="nav-item @if(($active ?? null) === 'boutique') active @endif"><i class="ti ti-building-store"></i> Ma boutique</a>
    <a href="{{ route('vendeur.produits.index') }}" class="nav-item @if(($active ?? null) === 'produits') active @endif"><i class="ti ti-package"></i> Produits </a>
    <a href="{{ route('vendeur.commandes') }}" class="nav-item @if(($active ?? null) === 'commandes') active @endif"><i class="ti ti-shopping-bag"></i> Commandes </a>
    <a href="{{ route('vendeur.notifications') }}" class="nav-item @if(($active ?? null) === 'notifications') active @endif">
        <i class="ti ti-bell"></i> Notifications
        <span class="admin-nav-badge" id="vendeur-notif-badge" style="{{ $vendeurNotifNonLues > 0 ? '' : 'display:none;' }}">{{ $vendeurNotifNonLues }}</span>
    </a>

    <div class="sidebar-section">Compte</div>
    <a href="{{ route('vendeur.parametres') }}" class="nav-item @if(($active ?? null) === 'parametres') active @endif"><i class="ti ti-user"></i> Profil</a>

    <div class="sidebar-footer">
        <div class="avatar-mini" data-avatar-display>
            @if (Auth::user()->avatar)
                <img src="{{ Auth::user()->avatarUrl() }}" alt="">
            @else
                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
            @endif
        </div>
        <div>
            <div class="name">{{ Auth::user()->boutique->nom ?? Auth::user()->name }}</div>
            <div class="role">Vendeur</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn" title="Se déconnecter"><i class="ti ti-logout"></i></button>
        </form>
    </div>
</aside>

<script>
    (function () {
        function actualiserBadgeVendeur() {
            fetch('{{ route('notifications.recentes') }}', { headers: { 'Accept': 'application/json' } })
                .then((r) => (r.ok ? r.json() : null))
                .then((data) => {
                    if (! data) return;
                    const badge = document.getElementById('vendeur-notif-badge');
                    if (! badge) return;
                    if (data.nonLues > 0) {
                        badge.textContent = data.nonLues;
                        badge.style.display = '';
                    } else {
                        badge.style.display = 'none';
                    }
                })
                .catch(() => {});
        }
        setInterval(actualiserBadgeVendeur, 15000);
    })();
</script>