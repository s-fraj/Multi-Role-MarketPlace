<x-admin-layout title="Tableau de bord — Admin" :active="'dashboard'">

    <h1 class="admin-page-title">Tableau de bord</h1>
    <div class="admin-page-sub">Vue d'ensemble de la plateforme</div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label"><i class="ti ti-users"></i> Clients</div>
            <div class="kpi-value">{{ $totalClients }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><i class="ti ti-building-store"></i> Vendeurs</div>
            <div class="kpi-value">{{ $totalVendeurs }}</div>
            <div class="kpi-sub">{{ $boutiquesActives }} boutique{{ $boutiquesActives > 1 ? 's' : '' }} active{{ $boutiquesActives > 1 ? 's' : '' }} · {{ $boutiquesInactives }} désactivée{{ $boutiquesInactives > 1 ? 's' : '' }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><i class="ti ti-shield-lock"></i> Comptes bloqués</div>
            <div class="kpi-value">{{ $comptesBloques }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><i class="ti ti-clock-exclamation"></i> Produits en attente</div>
            <div class="kpi-value">{{ $totalProduitsEnAttente }}</div>
            @if ($totalProduitsEnAttente > 0)
                <div class="kpi-sub"><a href="{{ route('admin.produits.en-attente') }}" style="color: var(--teal); font-weight:600;">Traiter la file →</a></div>
            @endif
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><i class="ti ti-package"></i> Produits en ligne</div>
            <div class="kpi-value">{{ $totalProduitsEnLigne }}</div>
            <div class="kpi-sub">{{ $totalProduitsRefuses }} refusé{{ $totalProduitsRefuses > 1 ? 's' : '' }}</div>
        </div>
    </div>

    <div class="admin-panel">
        <div class="admin-panel-head">
            <div class="admin-panel-title"><i class="ti ti-chart-bar"></i> Statistiques de ventes</div>
        </div>
        <div class="empty-state">
            <i class="ti ti-shopping-bag"></i>
            Pas encore de données de ventes — le système de commandes n'est pas encore en place.
        </div>
    </div>

</x-admin-layout>
