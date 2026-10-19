<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

<x-app-layout>

    @php
        
        $categories = [
            ['nom' => 'Mode & Textile', 'slug' => 'mode-textile', 'nombre_produits' => 1240],
            ['nom' => 'Maison & Artisanat', 'slug' => 'maison-artisanat', 'nombre_produits' => 860],
            ['nom' => 'Électronique', 'slug' => 'electronique', 'nombre_produits' => 2130],
            ['nom' => 'Beauté', 'slug' => 'beaute', 'nombre_produits' => 540],
            ['nom' => 'Épicerie fine', 'slug' => 'epicerie-fine', 'nombre_produits' => 310],
            ['nom' => 'Enfants', 'slug' => 'enfants', 'nombre_produits' => 680],
        ];

        $nouveautes = [
            ['nom' => 'Théière en cuivre gravée', 'vendeur' => 'Atelier Amira', 'prix' => 89, 'note' => 4.8, 'slug' => 'theiere-cuivre-gravee'],
            ['nom' => 'Écouteurs sans fil', 'vendeur' => 'TechSouk', 'prix' => 129, 'note' => 4.5, 'slug' => 'ecouteurs-sans-fil'],
            ['nom' => "Huile d'olive extra vierge, 1L", 'vendeur' => 'Ferme Zaytoun', 'prix' => 24, 'note' => 4.9, 'slug' => 'huile-olive-extra-vierge'],
            ['nom' => 'Sac en cuir tressé', 'vendeur' => 'Cuir & Co', 'prix' => 175, 'note' => 4.7, 'slug' => 'sac-cuir-tresse'],
        ];
    @endphp


    
    <form action="{{ route('produits.index') }}" method="GET" class="search-box">
        <i class="ti ti-search" style="color: var(--body-muted);"></i>
        <input type="text" name="recherche" placeholder="Rechercher un produit, une boutique..." />
    </form>
    

    <section class="block" id="categories">
        <h2 class="display">Parcourir par catégorie</h2>
        <div class="categories-grid" style="margin-top: 24px;">
            @foreach(($categories ?? []) as $categorie)
                <a href="{{ route('produits.index', ['categorie' => $categorie['slug']]) }}" class="category-card">
                    <div class="category-name">{{ $categorie['nom'] }}</div>
                    <div class="category-count">{{ $categorie['nombre_produits'] }} produits</div>
                </a>
            @endforeach
        </div>
    </section>

    <section class="block" style="padding-top: 8px;">
        <div class="section-head">
            <h2 class="display">Nouveautés des boutiques</h2>
            <a href="{{ route('produits.index', ['tri' => 'nouveautes']) }}" class="view-all">Tout voir →</a>
        </div>
        <div class="products-grid">
            @foreach(($nouveautes ?? []) as $produit)
                <a href="{{ route('produit-detail.index') }}" class="product-card">
                    <div class="product-thumb"></div>
                    <div class="product-body">
                        <div class="product-name">{{ $produit['nom'] }}</div>
                        <div class="product-vendor">{{ $produit['vendeur'] }}</div>
                        <div class="stall-row">
                            <span class="price">{{ $produit['prix'] }} DT</span>
                            <span class="rating"><i class="ti ti-star-filled"></i> {{ $produit['note'] }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
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

    
</x-app-layout>
