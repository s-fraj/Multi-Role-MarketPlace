<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
@vite('resources/css/produits.css')

<x-marketplace-layout title="Souq — Marketplace">

  @if (session('success'))
    <div style="margin: 16px 48px 0; padding: 12px 16px; border-radius: 10px; background: #1E5C6320; color: #1E5C63; font-size: 14px;">
      {{ session('success') }}
    </div>
  @endif

  <div class="top-bar">
    <div class="breadcrumb">Accueil / <span>{{ !empty($categoriesActives) ? implode(', ', $categoriesActives) : ($rechercheActive ? 'Résultats pour « '.$rechercheActive.' »' : 'Tous les produits') }}</span></div>
  </div>

  <div class="layout produits-page">
    <aside class="filters">
      <form method="GET" action="{{ route('produits.index') }}" id="filters-form">
        @if ($rechercheActive)
          <input type="hidden" name="recherche" value="{{ $rechercheActive }}" />
        @endif

        <div class="filter-group">
          <div class="filter-title" style="display:flex; justify-content:space-between; align-items:center;">
            Filtres <a href="{{ route('produits.index', $rechercheActive ? ['recherche' => $rechercheActive] : []) }}" class="btn-clear">Effacer</a>
          </div>
        </div>

        <div class="filter-group">
          <div class="filter-title">Catégorie</div>
          @foreach (\App\Models\Categorie::active()->orderBy('nom')->pluck('nom') as $cat)            <label class="filter-option">
            <input type="checkbox" name="categories[]" value="{{ $cat }}" onchange="document.getElementById('filters-form').submit();"
              @checked(in_array($cat, $categoriesActives ?? []))>
            {{ $cat }} <span class="count">{{ $comptesParCategorie[$cat] ?? 0 }}</span>
            </label>
          @endforeach
        </div>

        <div class="filter-group">
          <div class="filter-title">Prix (DT)</div>
          <div class="price-inputs">
            <input type="number" name="prix_min" placeholder="Min" min="0" value="{{ $prixMin }}" onchange="document.getElementById('filters-form').submit();" />
            <span style="color: var(--muted);">—</span>
            <input type="number" name="prix_max" placeholder="Max" min="0" value="{{ $prixMax }}" onchange="document.getElementById('filters-form').submit();" />
          </div>
        </div>

        <div class="filter-group">
          <div class="filter-title">Note minimum</div>
          <div class="rating-option" style="opacity:.5;" title="Bientôt disponible — les avis ne sont pas encore actifs">
            <input type="radio" name="note" disabled>2 et plus
          </div>
          <div class="rating-option" style="opacity:.5;" title="Bientôt disponible — les avis ne sont pas encore actifs">
            <input type="radio" name="note" disabled>3 et plus
          </div>
          <div class="rating-option" style="opacity:.5;" title="Bientôt disponible — les avis ne sont pas encore actifs">
            <input type="radio" name="note" disabled>4 et plus
          </div>
        </div>

        <div class="filter-group">
          <div class="filter-title">Disponibilité</div>
          <label class="filter-option">
            <input type="checkbox" name="disponible" value="1" onchange="document.getElementById('filters-form').submit();" @checked($disponibleActif)>
            En stock uniquement
          </label>
        </div>
      </form>
    </aside>

    <main>
      <div class="results-head">
        <div class="results-count">{{ $produits->count() }} produit{{ $produits->count() > 1 ? 's' : '' }} trouvé{{ $produits->count() > 1 ? 's' : '' }}</div>
      </div>
      
      <div class="products-grid">
        @forelse ($produits as $produit)
          <a href="{{ route('produit-detail.index', $produit) }}" class="product-card" style="display:block;">
            <div class="product-thumb" @if($produit->cover()) style="background-image:url('{{ $produit->cover()->url() }}'); background-size:cover; background-position:center;" @endif>
              @if ($produit->created_at?->gt(now()->subDays(7)))
                <span class="product-badge">Nouveau</span>
              @elseif ($produit->prix_barre)
                <span class="product-badge">Promo</span>
              @endif
              <div class="fav-btn @if(in_array($produit->id, $favorisIds ?? [])) active @endif" onclick="event.preventDefault(); event.stopPropagation(); toggleFavori({{ $produit->id }}, this);"><i class="ti ti-heart"></i></div>
            </div>
            <div class="product-body">
              <div class="product-vendor">{{ $produit->boutique->nom }}</div>
              <div class="product-name">{{ $produit->nom }}</div>
              <div class="product-rating"><i class="ti ti-star-filled"></i> — <span>(0 avis)</span></div>
              <div class="product-footer">
                <span class="price">{{ number_format($produit->prix, 0) }} DT</span>
                @guest
                  <span class="add-btn" type="button" data-produit-id="{{ $produit->id }}" onclick="event.preventDefault(); event.stopPropagation(); window.location.href = '{{ route('login') }}';"><i class="ti ti-plus"></i></span>
                @else
                  <span class="add-btn" type="button" data-produit-id="{{ $produit->id }}" onclick="event.preventDefault(); event.stopPropagation(); ajouterAuPanier({{ $produit->id }});"><i class="ti ti-plus"></i></span>
                @endguest
              </div>
            </div>
          </a>
        @empty
          <div style="grid-column: 1 / -1; text-align:center; padding: 48px 0; color: var(--muted);">
            @if ($rechercheActive || !empty($categoriesActives) || $prixMin || $prixMax || $disponibleActif)
              Aucun produit ne correspond à ces filtres.
            @else
              Aucun produit disponible pour le moment.
            @endif
          </div>
        @endforelse
      </div>
  
      
    </main>
  </div>
</x-marketplace-layout>