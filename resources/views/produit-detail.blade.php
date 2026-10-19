<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

@vite('resources/css/produit-detail.css')

<x-marketplace-layout title="{{ $produit->nom }} — Souq">

  @if (session('success'))
    <div style="margin: 0 0 16px; padding: 12px 16px; border-radius: 10px; background: #1E5C6320; color: #1E5C63; font-size: 14px;">
      {{ session('success') }}
    </div>
  @endif

  <div class="breadcrumb">Accueil / {{ $produit->categorie?->nom ?? 'Produits' }} / <span>{{ $produit->nom }}</span></div>

  <div class="product-layout">
    <div>
      @php $images = $produit->images; @endphp
      <div class="gallery-main" @if($images->isNotEmpty()) id="gallery-main" style="background-image:url('{{ $images->first()->url() }}'); background-size:cover; background-position:center;" @endif>
        <div class="fav-btn @if(in_array($produit->id, $favorisIds ?? [])) active @endif" onclick="toggleFavori({{ $produit->id }}, this);"><i class="ti ti-heart"></i></div>
      </div>
      @if ($images->isNotEmpty())
        <div class="thumbs">
          @foreach ($images as $i => $image)
              <div 
                  class="thumb @if($i === 0) active @endif"
                  style="background-image:url('{{ $image->url() }}'); background-size:cover; background-position:center;"
                  onclick="document.getElementById('gallery-main').style.backgroundImage = 'url({{ $image->url() }})'; document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active')); this.classList.add('active');">
              </div>
          @endforeach
        </div>
      @endif
    </div>

    <div>
      <div class="vendor-tag"><i class="ti ti-building-store"></i> {{ $produit->boutique->nom }}</div>
      <h1 class="product-title">{{ $produit->nom }}</h1>

      @php $moyenne = $produit->noteMoyenne(); $avisCount = $produit->avisCount(); @endphp
      <div class="rating-row">
        <div class="stars">
          @for ($i = 1; $i <= 5; $i++)
            <i class="ti ti-star{{ $i <= round($moyenne) ? ' filled' : '' }}"></i>
          @endfor
        </div>
        @if ($avisCount > 0)
          <span>{{ number_format($moyenne, 1) }}/5</span>
          <a href="#avis">{{ $avisCount }} avis</a>
        @else
          <span>Nouveau</span>
          <a href="#avis">0 avis</a>
        @endif
      </div>

      <div class="price-row">
        <span class="price-now">{{ number_format($produit->prix, 2) }} DT</span>
        @if ($produit->prix_barre)
          <span class="price-old">{{ number_format($produit->prix_barre, 2) }} DT</span>
          <span class="price-discount">-{{ round((1 - $produit->prix / $produit->prix_barre) * 100) }}%</span>
        @endif
      </div>

      <div class="stock-row">
        @if ($produit->stock > 0)
          <i class="ti ti-circle-check-filled"></i> En stock ({{ $produit->stock }} disponible{{ $produit->stock > 1 ? 's' : '' }}) — expédié sous 48h
        @else
          <i class="ti ti-circle-x-filled"></i> Rupture de stock
        @endif
      </div>

      @if (!empty($produit->variantes))
        @foreach ($produit->variantes as $vi => $variante)
          <div class="field" style="margin: 14px 0;">
            <label style="font-size:13px; font-weight:600; color: var(--teal-deep);">
              {{ $variante['nom'] }} : <span id="attribut-selection-{{ $vi }}">{{ $variante['valeurs'][0]['libelle'] ?? '' }}</span>
            </label>
            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:6px;">
              @foreach ($variante['valeurs'] as $vj => $valeur)
                @if ($valeur['photo'])
                  <span class="attribut-swatch @if($vj === 0) active @endif"
                        data-groupe="{{ $vi }}" data-libelle="{{ $valeur['libelle'] }}"
                        style="width:38px; height:38px; border-radius:8px; cursor:pointer; background-image:url('{{ asset('storage/'.$valeur['photo']) }}'); background-size:cover; background-position:center; border:2px solid {{ $vj === 0 ? 'var(--teal-deep)' : 'transparent' }};"
                        title="{{ $valeur['libelle'] }}"></span>
                @else
                  <span class="attribut-swatch @if($vj === 0) active @endif"
                        data-groupe="{{ $vi }}" data-libelle="{{ $valeur['libelle'] }}"
                        style="padding:6px 12px; border-radius:8px; font-size:13px; cursor:pointer; border:1px solid {{ $vj === 0 ? 'var(--teal-deep)' : 'var(--line)' }};">{{ $valeur['libelle'] }}</span>
                @endif
              @endforeach
            </div>
          </div>
        @endforeach
      @endif

      <div class="qty-row">
        <div class="qty-control">
          <button type="button" id="qty-decrease" {{ ($boutiqueIndisponible ?? false) ? 'disabled' : '' }}>−</button>
          <span id="qty-value">1</span>
          <button type="button" id="qty-increase" {{ ($boutiqueIndisponible ?? false) ? 'disabled' : '' }}>+</button>
        </div>
      </div>

      @if ($boutiqueIndisponible ?? false)
        <div style="margin: 0 0 16px; padding: 12px 16px; border-radius: 10px; background: #C0392B20; color: #C0392B; font-size: 14px; display:flex; align-items:center; gap:8px;">
          <i class="ti ti-alert-triangle"></i> Cette boutique n'est plus disponible pour le moment — achat désactivé.
        </div>
      @endif

      <div class="cta-row">
        <button class="btn-cart" id="btn-add-cart" data-produit-id="{{ $produit->id }}" {{ ($produit->stock <= 0 || ($boutiqueIndisponible ?? false)) ? 'disabled' : '' }}><i class="ti ti-shopping-cart-plus"></i> Ajouter au panier</button>
        <button class="btn-buy" id="btn-buy-now" data-produit-id="{{ $produit->id }}" {{ ($produit->stock <= 0 || ($boutiqueIndisponible ?? false)) ? 'disabled' : '' }}>Acheter maintenant</button>
      </div>

      <div class="trust-mini">
        <div class="trust-mini-item"><i class="ti ti-shield-check"></i> Paiement sécurisé</div>
        <div class="trust-mini-item"><i class="ti ti-truck-delivery"></i> Livré par First Delivery</div>
        <div class="trust-mini-item"><i class="ti ti-rotate"></i> Retour sous 7 jours</div>
      </div>
    </div>
  </div>

  <div class="section-block">
    <div class="section-title">Description</div>
    @if ($produit->description)
      <p class="desc-text">{{ $produit->description }}</p>
    @else
      <p class="desc-text">Aucune description fournie pour ce produit.</p>
    @endif
    <table class="specs-table">
      @if ($produit->categorie)
        <tr><td>Catégorie</td><td>{{ $produit->categorie?->nom }}</td></tr>
      @endif
      @if ($produit->sku)
        <tr><td>Référence</td><td>{{ $produit->sku }}</td></tr>
      @endif
      @if ($produit->poids)
        <tr><td>Poids</td><td>{{ $produit->poids }} kg</td></tr>
      @endif
      <tr><td>Boutique</td><td>{{ $produit->boutique->nom }}</td></tr>
    </table>
  </div>

  <div class="section-block" id="avis">
    <div class="section-title">Avis clients ({{ $produit->avisCount() }})</div>

    @if ($monAvis)
      @include('partials.avis-mine', ['avis' => $monAvis])
    @elseif ($peutNoter)
      <form method="POST" action="{{ route('avis.store', $produit) }}" enctype="multipart/form-data" style="margin-bottom:20px;">
        @csrf

        @if ($errors->any())
          <div style="margin-bottom:12px; padding:10px 14px; border-radius:8px; background:#FBE7E5; color:#A32F26; font-size:13px;">
            @foreach ($errors->all() as $erreur)
              <div>{{ $erreur }}</div>
            @endforeach
          </div>
        @endif

        <div class="field" style="margin-bottom:12px;">
          <label>Ta note</label>
          @include('partials.star-input', ['suffix' => 'new', 'valeurInitiale' => 0])
        </div>

        <div class="field" style="margin-bottom:12px;">
          <label>Ton commentaire</label>
          <textarea name="commentaire" rows="3" placeholder="Qu'as-tu pensé de ce produit ?" required></textarea>
        </div>

        <div class="field" style="margin-bottom:12px;">
          <label>Photos <span style="color: var(--muted); font-weight:400;">(au moins une)</span></label>
          <div class="avis-photo-row" id="avis-photo-row-new">
            <label class="avis-camera-btn" title="Ajouter une photo">
              <i class="ti ti-camera"></i>
              <input type="file" accept="image/*" multiple style="display:none;" id="avis-photos-input-new">
            </label>
          </div>
          <input type="file" name="photos[]" id="avis-photos-real-new" multiple style="display:none;">
        </div>

        <button type="submit" class="btn btn-primary" style="font-size:13px; padding:8px 16px;"><i class="ti ti-send"></i> Publier l'avis</button>
      </form>
    @else
      <p class="desc-text" style="font-size:13px; color:var(--muted);">Tu pourras laisser un avis une fois ce produit livré.</p>
    @endif

    @php $autresAvis = $produit->avis->where('user_id', '!=', optional(Auth::user())->id); @endphp

    @if ($autresAvis->isNotEmpty())
      <div class="avis-list">
        @foreach ($autresAvis as $avisItem)
          @include('partials.avis-item', ['avisItem' => $avisItem])
        @endforeach
      </div>
    @elseif (! $monAvis)
      <p class="desc-text">Aucun avis pour le moment. Sois le premier à donner ton avis sur ce produit.</p>
    @endif
  </div>

  <script>
    (function () {
      const produitId = {{ $produit->id }};
      const maxStock = {{ (int) $produit->stock }};
      const isGuest = {{ auth()->check() ? 'false' : 'true' }};
      const loginUrl = "{{ route('login') }}";
      const qtyValue = document.getElementById('qty-value');
      const decrease = document.getElementById('qty-decrease');
      const increase = document.getElementById('qty-increase');
      const btnCart = document.getElementById('btn-add-cart');
      const btnBuy = document.getElementById('btn-buy-now');

      function getQty() {
        return parseInt(qtyValue.textContent, 10) || 1;
      }

      decrease.addEventListener('click', function () {
        const next = Math.max(1, getQty() - 1);
        qtyValue.textContent = next;
      });

      increase.addEventListener('click', function () {
        const next = maxStock ? Math.min(maxStock, getQty() + 1) : getQty() + 1;
        qtyValue.textContent = next;
      });

      btnCart.addEventListener('click', function () {
        if (isGuest) {
          window.location.href = loginUrl;
          return;
        }
        ajouterAuPanier(produitId, getQty());
      });

      btnBuy.addEventListener('click', function () {
        if (isGuest) {
          window.location.href = loginUrl;
          return;
        }
        ajouterAuPanier(produitId, getQty(), function () {
          window.location.href = "{{ route('panier.index') }}";
        });
      });

      // Attribute swatches (color photo, size chip...): clicking one updates
      // the "Nom : valeur" label above its row and highlights the pick.
      document.querySelectorAll('.attribut-swatch').forEach(function (swatch) {
        swatch.addEventListener('click', function () {
          const groupe = swatch.dataset.groupe;
          document.getElementById('attribut-selection-' + groupe).textContent = swatch.dataset.libelle;

          document.querySelectorAll('.attribut-swatch[data-groupe="' + groupe + '"]').forEach(function (s) {
            const estPhoto = !! s.style.backgroundImage;
            s.style.border = '2px solid transparent';
            if (! estPhoto) s.style.border = '1px solid var(--line)';
          });
          swatch.style.border = '2px solid var(--teal-deep)';
        });
      });
    })();
  </script>

  @include('partials.avis-scripts')
</x-marketplace-layout>
