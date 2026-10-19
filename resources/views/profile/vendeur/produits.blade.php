<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
<x-marketplace-layout title="Souk — Produits">

  <div class="app">
    @include('profile.vendeur.partials.sidebar', ['active' => 'produits'])

    <main class="main">
      <div class="top-row">
        <h1 class="page-title">Mes produits</h1>
        <div class="header-actions">
          <a href="{{ route('vendeur.boutique') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Ajouter un produit</a>
        </div>
      </div>

      <div class="products-grid" style="margin-top: 6px;">
        @forelse ($produits as $produit)
          @php $badge = $produit->statutBadge(); @endphp
          <div class="product-card" id="produit-{{ $produit->id }}" style="display: flex; flex-direction: column;">
            <div class="product-thumb" style="position: relative; @if($produit->cover()) background-image:url('{{ $produit->cover()->url() }}'); background-size:cover; background-position:center; @endif">
              <span class="statut-pill {{ $badge['classe'] }}" style="position: absolute; top: 10px; left: 10px;">{{ $badge['texte'] }}</span>
            </div>
            <div class="product-body" style="flex: 1; display: flex; flex-direction: column;">
              <div class="product-name">{{ $produit->nom }}</div>
              <div class="product-vendor">{{ $produit->categorie?->nom ?? 'Sans catégorie' }}</div>
              <div class="stall-row" style="margin-bottom: 10px;">
                <span class="price">{{ number_format($produit->prix, 2) }} DT</span>
                <span class="rating"><i class="ti ti-box"></i> Stock : {{ $produit->stock }}</span>
              </div>
              @if ($produit->statut === 'refuse' && $produit->raison_refus)
                <div class="desc" style="color: var(--terracotta); font-size: 12px; margin-bottom: 10px;"><i class="ti ti-info-circle"></i> {{ $produit->raison_refus }}</div>
              @endif
              <div style="margin-top: auto; display: flex; gap: 6px; flex-wrap: wrap;">
                @if ($produit->statut === 'en_ligne')
                  <a href="{{ route('produit-detail.index', $produit) }}" class="admin-btn admin-btn-outline" style="flex: 1; justify-content: center;">Voir</a>
                @endif
                <a href="{{ route('vendeur.produits.edit', $produit) }}" class="admin-btn admin-btn-outline" style="flex: 1; justify-content: center;">Modifier</a>
                <button type="button" class="admin-btn admin-btn-refuse" style="flex: 1; justify-content: center;" onclick="supprimerProduit({{ $produit->id }})">Supprimer</button>
              </div>
            </div>
          </div>
        @empty
          <div class="empty-state" style="grid-column: 1 / -1;">
            <i class="ti ti-package"></i>
            Tu n'as pas encore ajouté de produit.
            <div style="margin-top: 12px;">
              <a href="{{ route('vendeur.boutique') }}" class="btn btn-primary" style="display:inline-flex;"><i class="ti ti-plus"></i> Ajouter un produit</a>
            </div>
          </div>
        @endforelse
      </div>
    </main>
  </div>

  <script>
    function supprimerProduit(produitId) {
      if (! confirm('Supprimer ce produit ? Cette action est définitive.')) return;

      fetch(`/vendeur/produits/${produitId}`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
      })
        .then(async (r) => {
          const data = await r.json();
          if (! r.ok || ! data.success) {
            throw new Error(data.message || 'Erreur');
          }
          showToast(data.message, 'success');
          const el = document.getElementById(`produit-${produitId}`);
          if (el) el.remove();
        })
        .catch((err) => showToast(err.message || 'Erreur lors de la suppression.', 'error'));
    }
  </script>

</x-marketplace-layout >