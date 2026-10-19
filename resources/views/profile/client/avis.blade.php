<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

@vite(['resources/css/panier.css', 'resources/css/produit-detail.css'])

<x-marketplace-layout title="Mes avis — Souq">

    <div class="souq-app compte-layout">
        @include('profile.client.partials.sidebar', ['active' => 'avis'])

        <main>
            <h1 class="page-title">Mes avis</h1>
            <div class="page-sub">{{ $mesAvis->count() }} avis publié{{ $mesAvis->count() > 1 ? 's' : '' }}</div>

            @if ($mesAvis->isEmpty())
                <div class="empty-hint" style="margin-top: 24px;">
                    <i class="ti ti-star"></i> Tu n'as pas encore laissé d'avis.
                    <a href="{{ route('client.commandes') }}" style="color: var(--teal); font-weight:600;">Voir mes commandes →</a>
                </div>
            @else
                <div style="margin-top: 20px; display:flex; flex-direction:column; gap: 20px;">
                    @foreach ($mesAvis as $avisItem)
                        @php $produit = $avisItem->produit; @endphp
                        <div class="panel" style="padding: 18px 20px;">
                            <a href="{{ route('produit-detail.index', $produit) }}" class="cart-item" style="text-decoration:none; margin-bottom: 12px;">
                                <span class="item-thumb" @if($produit->cover()) style="background-image:url('{{ $produit->cover()->url() }}'); background-size:cover; background-position:center; display:block;" @endif></span>
                                <div class="item-info">
                                    <span class="item-name">{{ $produit->nom }}</span>
                                    <div class="item-meta">{{ $produit->boutique->nom }}</div>
                                </div>
                            </a>

                            @include('partials.avis-mine', ['avis' => $avisItem])
                        </div>
                    @endforeach
                </div>
            @endif
        </main>
    </div>

    @include('partials.avis-scripts')

</x-marketplace-layout>
