

@php
    $user = Auth::user();

    $nameParts = collect(preg_split('/\s+/', trim($user->name)))->filter();
    $initials = $nameParts->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
    $firstName = $nameParts->first() ?? $user->name;

    
    $stats = $stats ?? [
        'en_cours' => 2,
        'livrees'  => 14,
        'avis'     => 3,
        'litiges'  => 0,
    ];
    $recentOrders = $recentOrders ?? collect([
        (object) [
            'product_name' => 'Théière en cuivre gravée',
            'reference'    => 'SOQ-24817',
            'vendor_name'  => 'Atelier Amira',
            'date_label'   => '12 juil. 2026',
            'status_slug'  => 'preparation',
            'status_label' => 'En préparation',
            'total'        => 89,
            'action_label' => 'Suivre',
        ],
        (object) [
            'product_name' => "Huile d'olive extra vierge, 1L",
            'reference'    => 'SOQ-24802',
            'vendor_name'  => 'Ferme Zaytoun',
            'date_label'   => '10 juil. 2026',
            'status_slug'  => 'expediee',
            'status_label' => 'Expédiée',
            'total'        => 48,
            'action_label' => 'Suivre',
        ],
        (object) [
            'product_name' => 'Sac en cuir tressé',
            'reference'    => 'SOQ-24741',
            'vendor_name'  => 'Cuir & Co',
            'date_label'   => '2 juil. 2026',
            'status_slug'  => 'livree',
            'status_label' => 'Livrée',
            'total'        => 175,
            'action_label' => 'Laisser un avis',
        ],
    ]);
@endphp
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

<x-marketplace-layout title="Mon compte — Souq">

    <div class="souq-app compte-layout">
      @include('profile.client.partials.sidebar', ['active' => 'compte'])
        <main>
            <h1 class="page-title">Bonjour, {{ $firstName }}</h1>
            <div class="page-sub">Voici un résumé de ton activité sur Souq</div>

            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="ti ti-clock"></i></div>
                    <div class="stat-value">{{ $stats['en_cours'] }}</div>
                    <div class="stat-label">Commandes en cours</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon teal"><i class="ti ti-circle-check"></i></div>
                    <div class="stat-value">{{ $stats['livrees'] }}</div>
                    <div class="stat-label">Commandes livrées</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon ochre"><i class="ti ti-star"></i></div>
                    <div class="stat-value">{{ $stats['avis'] }}</div>
                    <div class="stat-label">Avis à laisser</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon coral"><i class="ti ti-alert-triangle"></i></div>
                    <div class="stat-value">{{ $stats['litiges'] }}</div>
                    <div class="stat-label">Litige en cours</div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">Commandes récentes</div>
                    <a href="#" class="view-all">Voir toutes mes commandes →</a>
                </div>

                @forelse ($recentOrders as $order)
                    <div class="order-row">
                        <div class="order-thumb"></div>
                        <div class="order-info">
                            <div class="order-name">{{ $order->product_name }}</div>
                            <div class="order-meta">#{{ $order->reference }} · {{ $order->vendor_name }} · {{ $order->date_label }}</div>
                        </div>
                        <span class="status-pill {{ $order->status_slug }}">{{ $order->status_label }}</span>
                        <div class="order-price">{{ $order->total }} DT</div>
                        <a href="#" class="order-action">{{ $order->action_label }} →</a>
                    </div>
                @empty
                    <div class="panel-empty">
                        Tu n'as pas encore de commande. <a href="{{ route('produits.index') }}">Découvrir les produits →</a>
                    </div>
                @endforelse
            </div>

            <div class="quick-links">
                <a href="#" class="quick-link-card">
                    <i class="ti ti-map-pin"></i>
                    <div>
                        <div class="quick-link-title">Adresses</div>
                        <div class="quick-link-sub">2 adresses enregistrées</div>
                    </div>
                </a>
                <a href="#" class="quick-link-card">
                    <i class="ti ti-heart"></i>
                    <div>
                        <div class="quick-link-title">Favoris</div>
                        <div class="quick-link-sub">9 produits sauvegardés</div>
                    </div>
                </a>
                <a href="{{ route('profile.modif-profile') }}" class="quick-link-card">
                    <i class="ti ti-settings"></i>
                    <div>
                        <div class="quick-link-title">Paramètres</div>
                        <div class="quick-link-sub">Mot de passe, notifications</div>
                    </div>
                </a>
            </div>
        </main>
    </div>

</x-marketplace-layout>
