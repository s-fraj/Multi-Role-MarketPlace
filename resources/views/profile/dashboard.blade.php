<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">



<x-marketplace-layout title="Souk — Tableau de bord">

    <section class="block" style="padding-bottom: 0;">
        <h1 class="display" style="font-size: 28px; margin-bottom: 4px;">
            Bonjour, {{ Str::before(Auth::user()->name, ' ') }} 👋
        </h1>
        <p style="color: var(--body-muted); margin-bottom: 8px;">
            @if (Auth::user()->isVendeur())
                Retrouve ta boutique, tes ventes et tes paramètres depuis ton espace vendeur.
            @else
                Retrouve tes commandes, ton panier et tes informations depuis ton espace client.
            @endif
        </p>
        <a href="{{ route(Auth::user()->profileRouteName()) }}" class="btn btn-line" style="display:inline-flex; margin-top: 12px;">
            <i class="ti ti-user"></i> Aller à mon profil
        </a>
    </section>

   
    <section class="block" id="categories">
        <h2 class="display">Parcourir par catégorie</h2>
        <div class="categories-grid" style="margin-top: 24px;">
            @forelse(($categories ?? []) as $categorie)
                <a href="{{ route('produits.index', ['categorie' => $categorie->nom]) }}" class="category-card">
                    <div class="category-name">{{ $categorie->nom }}</div>
                    <div class="category-count">{{ $categorie->produits_count }} produit{{ $categorie->produits_count > 1 ? 's' : '' }}</div>
                </a>
            @empty
                <p style="color: var(--muted);">Aucune catégorie disponible pour le moment.</p>
            @endforelse
        </div>
    </section>

    <section class="block" style="padding-top: 8px;">
        <div class="section-head">
            <h2 class="display">Nouveautés des boutiques</h2>
            <a href="{{ route('produits.index', ['tri' => 'nouveautes']) }}" class="view-all">Tout voir →</a>
        </div>
        <div class="products-grid">
            @forelse(($nouveautes ?? []) as $produit)
                <a href="{{ route('produit-detail.index', $produit) }}" class="product-card">
                    <div class="product-thumb" @if($produit->cover()) style="background-image:url('{{ $produit->cover()->url() }}'); background-size:cover; background-position:center;" @endif></div>
                    <div class="product-body">
                        <div class="product-name">{{ $produit->nom }}</div>
                        <div class="product-vendor">{{ $produit->boutique->nom }}</div>
                        <div class="stall-row">
                            <span class="price">{{ number_format($produit->prix, 0) }} DT</span>
                            <span class="rating"><i class="ti ti-star-filled"></i> Nouveau</span>
                        </div>
                    </div>
                </a>
            @empty
                <p style="color: var(--muted);">Aucun produit publié pour le moment.</p>
            @endforelse
        </div>
    </section>

    <section class="trust">
        <div class="trust-grid">
            <div class="trust-item">
                <i class="ti ti-shield-check"></i>
                <div class="trust-title">Paiement sécurisé</div>
                <div class="trust-text">Chaque transaction est protégée, du panier à la livraison.</div>
            </div>
            <div class="trust-item">
                <i class="ti ti-truck-delivery"></i>
                <div class="trust-title">Suivi en temps réel</div>
                <div class="trust-text">Suis ta commande, étape par étape, jusqu'à ta porte.</div>
            </div>
            <div class="trust-item">
                <i class="ti ti-headset"></i>
                <div class="trust-title">Support vendeurs</div>
                <div class="trust-text">Une équipe dédiée aide chaque boutique à grandir.</div>
            </div>
        </div>
    </section>

</x-marketplace-layout>
