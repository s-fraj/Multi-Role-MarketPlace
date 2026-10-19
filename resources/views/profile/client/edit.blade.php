@php
    $user = Auth::user();

    $nameParts = collect(preg_split('/\s+/', trim($user->name)))->filter();
    $initials = $nameParts->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
    $firstName = $nameParts->first() ?? $user->name;
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
                    <div class="stat-value">{{ $stats['favori'] }}</div>
                    <div class="stat-label">Favoris</div>
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
                    <a href="{{ route('client.commandes') }}"  class="view-all"><i class="ti ti-package"></i>Voir toutes mes commandes →</a>
                </div>

                @forelse ($recentOrders as $order)
                    <div class="order-row">
                        <div class="order-thumb" @if(!empty($order->cover_url)) style="background-image:url('{{ $order->cover_url }}'); background-size:cover; background-position:center;" @endif></div>
                        <div class="order-info">
                            <div class="order-name">{{ $order->product_name }}</div>
                            <div class="order-meta">#{{ $order->reference }} · {{ $order->vendor_name }} · {{ $order->date_label }}</div>
                        </div>
                        <span class="status-pill {{ $order->status_slug }}">{{ $order->status_label }}</span>
                        <div class="order-price">{{ $order->total }} DT</div>
                        @if ($order->action_label === 'Laisser un avis' && $order->produit_id)
                          <a href="{{ route('produit-detail.index', $order->produit_id) }}#avis" class="order-action">{{ $order->action_label }} →</a>
                        @else
                          <a href="{{ route('commandes.suivi', $order->id) }}" class="order-action">{{ $order->action_label }} →</a>
                        @endif
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
                        <div class="quick-link-sub">1 adresse enregistrée</div>
                    </div>
                </a>
                <a href="#" class="quick-link-card">
                    <i class="ti ti-heart"></i>
                    <div>
                        <div class="quick-link-title">Favoris</div>
                        <div class="quick-link-sub">{{ $stats['favori'] }} produit{{  $stats['favori']  > 1 ? 's' : '' }} sauvegardé{{  $stats['favori'] > 1 ? 's' : '' }}</div>
                    </div>
                </a>
                <a href="{{ route('profile.edit') }}" class="quick-link-card">
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
