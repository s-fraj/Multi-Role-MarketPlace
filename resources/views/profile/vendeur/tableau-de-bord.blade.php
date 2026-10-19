<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
<x-marketplace-layout title="Souk — Tableau-de-bord">

  <div class="app">
    @include('profile.vendeur.partials.sidebar', ['active' => 'tableau-de-bord'])

    <main class="main">
      @php
        $nameParts = collect(preg_split('/\s+/', trim(Auth::user()->name)))->filter();
        $firstName = $nameParts->first() ?? Auth::user()->name;
      @endphp

      <h1 class="page-title">Bonjour, {{ $firstName }}</h1>
      <div class="page-sub">Voici un résumé de ton activité vendeur sur Souq</div>

      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="ti ti-clock"></i></div>
          <div class="stat-value">{{ $stats['en_cours'] }}</div>
          <div class="stat-label">Commandes en cours</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon teal"><i class="ti ti-circle-check"></i></div>
          <div class="stat-value">{{ $stats['livrees'] }}</div>
          <div class="stat-label">Commandes finalisées</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon ochre"><i class="ti ti-package"></i></div>
          <div class="stat-value">{{ $stats['produits_actifs'] }}</div>
          <div class="stat-label">Produits en ligne</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon coral"><i class="ti ti-bell"></i></div>
          <div class="stat-value">{{ $stats['notifications_non_lues'] }}</div>
          <div class="stat-label">Notifications</div>
        </div>
      </div>

      <div class="panel">
        <div class="panel-head">
          <div class="panel-title">Commandes récentes</div>
          <a href="{{ route('vendeur.commandes') }}" class="view-all">Voir toutes mes commandes →</a>
        </div>

        @forelse ($recentOrders as $order)
          <div class="order-row">
            <div class="order-info">
              <div class="order-name">Commande {{ $order->reference }}</div>
              <div class="order-meta">
                {{ $order->nom_client }} · {{ $order->items->sum('quantite') }} article(s) · {{ $order->created_at->format('d/m/Y') }}
              </div>
            </div>
            @php
              $pillClass = match($order->statut) {
                'en_attente' => 'attente',
                'en_cours' => 'preparation',
                'confirmee' => 'livree',
                'annulee' => 'litige',
                default => 'attente',
              };
              $pillLabel = match($order->statut) {
                'en_attente' => 'En attente',
                'en_cours' => 'En cours',
                'confirmee' => 'Finalisée',
                'annulee' => 'Annulée',
                default => $order->statut,
              };
            @endphp
            <span class="status-pill {{ $pillClass }}">{{ $pillLabel }}</span>
            <div class="order-price">{{ number_format($order->total, 2) }} DT</div>
          </div>
        @empty
          <div class="panel-empty">
            Aucune commande pour le moment.
          </div>
        @endforelse
      </div>

      <div class="quick-links">
        <a href="{{ route('vendeur.boutique') }}" class="quick-link-card">
          <i class="ti ti-building-store"></i>
          <div>
            <div class="quick-link-title">Ma boutique</div>
            <div class="quick-link-sub">Gérer les informations</div>
          </div>
        </a>
        <a href="{{ route('vendeur.produits.index') }}" class="quick-link-card">
          <i class="ti ti-package"></i>
          <div>
            <div class="quick-link-title">Produits</div>
            <div class="quick-link-sub">{{ $stats['produits_actifs'] }} produits en ligne</div>
          </div>
        </a>
        <a href="{{ route('vendeur.parametres') }}" class="quick-link-card">
          <i class="ti ti-settings"></i>
          <div>
            <div class="quick-link-title">Paramètres</div>
            <div class="quick-link-sub">Profil, sécurité</div>
          </div>
        </a>
      </div>
    </main>
  </div>

</x-marketplace-layout >