<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

@vite('resources/css/panier.css')

<x-marketplace-layout title="Mes favoris — Souq">

    <div class="souq-app compte-layout">
        @include('profile.client.partials.sidebar', ['active' => 'favoris'])

        <main>
            <h1 class="page-title">Mes favoris</h1>
            <div class="page-sub">{{ $favoris->count() }} produit{{ $favoris->count() > 1 ? 's' : '' }} enregistré{{ $favoris->count() > 1 ? 's' : '' }}</div>

            @if ($favoris->isEmpty())
                <div class="empty-hint" style="margin-top: 24px;">
                    <i class="ti ti-heart"></i> Tu n'as pas encore de favoris.
                    <a href="{{ route('produits.index') }}" style="color: var(--teal); font-weight:600;">Découvrir les produits →</a>
                </div>
            @else
                <div class="vendor-group" style="margin-top: 20px;">
                    @foreach ($favoris as $favori)
                        @php $produit = $favori->produit; @endphp
                        <div class="cart-item" id="favori-item-{{ $favori->id }}">
                            <a href="{{ route('produit-detail.index', $produit) }}" class="item-thumb" @if($produit->cover()) style="background-image:url('{{ $produit->cover()->url() }}'); background-size:cover; background-position:center; display:block;" @endif></a>
                            <div class="item-info">
                                <a href="{{ route('produit-detail.index', $produit) }}" class="item-name" style="display:block;">{{ $produit->nom }}</a>
                                <div class="item-meta">{{ $produit->boutique->nom }}</div>
                            </div>
                            <div class="item-price">{{ number_format($produit->prix, 2) }} DT</div>
                            <div class="item-favori-remove" onclick="retirerFavori({{ $produit->id }}, {{ $favori->id }})" title="Retirer des favoris"><i class="ti ti-heart"></i></div>
                        </div>
                    @endforeach
                </div>
            @endif
        </main>
    </div>

    <script>
        function retirerFavori(produitId, favoriId) {
            const row = document.getElementById(`favori-item-${favoriId}`);
            toggleFavori(produitId, null, function (estFavori) {
                if (! estFavori) {
                    row.remove();
                }
            });
        }
    </script>

</x-marketplace-layout>
