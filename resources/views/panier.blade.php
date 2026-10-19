<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@vite('resources/css/panier.css')

<x-marketplace-layout title="Souq — Panier">

  <div class="panier-topbar">
    <a href="{{ route('produits.index') }}" class="back-link"><i class="ti ti-arrow-left"></i> Continuer mes achats</a>
  </div>

  @php $nbArticles = $groupes->flatten()->sum('quantite'); @endphp

  <div class="page-head">
    <h1 class="page-title">Mon panier</h1>
    <div class="page-sub">{{ $nbArticles }} article{{ $nbArticles > 1 ? 's' : '' }} de {{ $groupes->count() }} boutique{{ $groupes->count() > 1 ? 's' : '' }} différente{{ $groupes->count() > 1 ? 's' : '' }}</div>
  </div>

  <div class="steps">
    <div class="step active"><div class="step-dot">1</div> Panier</div>
    <div class="step-sep"></div>
    <div class="step" id="step-2"><div class="step-dot">2</div> Livraison</div>
    <div class="step-sep"></div>
    <div class="step" id="step-3"><div class="step-dot">3</div> Confirmation</div>
  </div>

  @if ($groupes->isEmpty())

    <div class="empty-hint" style="margin-top: 24px;">
      <i class="ti ti-shopping-cart"></i> Ton panier est vide pour le moment.
      <a href="{{ route('produits.index') }}" style="color: var(--teal); font-weight:600;">Découvrir les produits →</a>
    </div>

  @else

    {{-- CART VIEW --}}
    <div id="cart-view" class="cart-layout">
      <div>
        @foreach ($groupes as $nomBoutique => $items)
          <div class="vendor-group">
            <div class="vendor-header"><i class="ti ti-building-store"></i> {{ $nomBoutique }}</div>

            @foreach ($items as $item)
              <div class="cart-item" id="cart-item-{{ $item->id }}">
                <div class="item-thumb" @if($item->produit->cover()) style="background-image:url('{{ $item->produit->cover()->url() }}'); background-size:cover; background-position:center;" @endif></div>
                <div class="item-info">
                  <div class="item-name">{{ $item->produit->nom }}</div>
                  <div class="item-meta">{{ $item->produit->sku ? 'Réf. '.$item->produit->sku : '' }}</div>
                </div>
                <div class="item-qty">
                  <button type="button" onclick="changerQuantite({{ $item->id }}, -1)">−</button>
                  <span id="item-qty-{{ $item->id }}">{{ $item->quantite }}</span>
                  <button type="button" onclick="changerQuantite({{ $item->id }}, 1)">+</button>
                </div>
                <div class="item-price" id="item-price-{{ $item->id }}">{{ number_format($item->sousTotal(), 2) }} DT</div>
                <div class="item-remove" onclick="supprimerArticle({{ $item->id }})"><i class="ti ti-trash"></i></div>
              </div>
            @endforeach

            <div class="vendor-subtotal"><span>Livraison via First Delivery</span><span>7 DT</span></div>
          </div>
        @endforeach

        <div class="empty-hint"><i class="ti ti-info-circle"></i> Chaque boutique est livrée séparément — les frais de livraison s'appliquent par vendeur.</div>
      </div>

      <div class="summary-card">
        <div class="summary-title">Récapitulatif</div>
        <div class="summary-row"><span>Sous-total ({{ $nbArticles }} article{{ $nbArticles > 1 ? 's' : '' }})</span><span id="summary-sous-total">{{ number_format($sousTotal, 2) }} DT</span></div>
        <div class="summary-row"><span>Livraison ({{ $groupes->count() }} boutique{{ $groupes->count() > 1 ? 's' : '' }})</span><span id="summary-livraison">{{ number_format($livraison, 2) }} DT</span></div>
        <div class="summary-row"><span>Remise</span><span>−0 DT</span></div>

        

        <div class="summary-row total"><span>Total</span><span class="val" id="summary-total">{{ number_format($total, 2) }} DT</span></div>

        <button class="btn-checkout" onclick="showCheckout()">Passer à la livraison <i class="ti ti-arrow-right"></i></button>
        <div class="secure-note"><i class="ti ti-lock"></i> Paiement sécurisé, fonds protégés jusqu'à réception</div>
      </div>
    </div>

    {{-- CHECKOUT VIEW (hidden by default) --}}
    <div id="checkout-view" style="display:none; max-width: 560px; margin: 0 auto; padding: 8px 48px 64px;">
      <div class="panel">
        <div class="panel-title">Adresse de livraison</div>

        <form method="POST" action="{{ route('commandes.store') }}" onsubmit="return validerAdresseAvantEnvoi(event);">
          @csrf
          <div class="field">
            <label>Nom complet</label>
            <input type="text" name="nom" value="{{ Auth::user()->name }}" required />
          </div>
          <div class="row-2">
            <div class="field">
              <label>Téléphone</label>
              <input type="tel" name="telephone" value="{{ Auth::user()->phone }}" required placeholder="20 000 000" />
            </div>
            <div class="field">
              <label>Code postal <span style="color: var(--muted); font-weight: 400;">(optionnel)</span></label>
              <input type="text" name="code_postal" id="code_postal-input" value="{{ Auth::user()->postal_code }}" maxlength="10" placeholder="1000" />
            </div>
          </div>

          <input type="hidden" name="latitude" id="latitude-input" />
          <input type="hidden" name="longitude" id="longitude-input" />
          <input type="hidden" name="ville" id="ville-input" />

          <div class="field">
            <label>Adresse de livraison</label>
            <div id="adresse-picker" class="address-picker" onclick="ouvrirCarte()">
              <i class="ti ti-map-pin"></i>
              <span id="adresse-picker-text">Cliquez pour choisir l'emplacement sur la carte</span>
            </div>
            <input type="hidden" name="adresse" id="adresse-input" />
          </div>

          <div style="border-top: 1px solid var(--line); margin: 20px 0; padding-top: 16px;">
            <div class="summary-row total" style="border:none; padding:0; margin-bottom: 16px;">
              <span>Total à payer</span>
              <span class="val">{{ number_format($total, 2) }} DT</span>
            </div>
            <button type="submit" class="btn-checkout" style="margin-top:0;">Confirmer le paiement <i class="ti ti-lock"></i></button>
            <button type="button" class="btn btn-ghost" style="width:100%; margin-top:10px;" onclick="showCart()">Retour au panier</button>
          </div>
        </form>
      </div>
    </div>

    {{-- MAP PICKER MODAL --}}
    <div id="map-modal-overlay" class="map-modal-overlay" style="display:none;">
      <div class="map-modal">
        <div class="map-modal-header">
          <div class="panel-title" style="margin:0;">Choisis l'emplacement de livraison</div>
          <span class="map-modal-close" onclick="fermerCarte()"><i class="ti ti-x"></i></span>
        </div>
        <div class="map-modal-hint"><i class="ti ti-info-circle"></i> Clique sur la carte (ou déplace le repère) pour placer le point de livraison.</div>
        <div id="delivery-map"></div>
        <div class="map-modal-footer">
          <span id="map-selected-hint" class="map-selected-hint">Aucun emplacement choisi</span>
          <div style="display:flex; gap:10px;">
            <button type="button" class="btn btn-ghost" onclick="fermerCarte()">Annuler</button>
            <button type="button" class="btn btn-primary" id="map-confirm-btn" disabled onclick="confirmerEmplacement()">Confirmer l'emplacement</button>
          </div>
        </div>
      </div>
    </div>

  @endif

  <script>
    function showCheckout() {
      document.getElementById('cart-view').style.display = 'none';
      document.getElementById('checkout-view').style.display = 'block';
      document.getElementById('step-2').classList.add('active');
    }
    function showCart() {
      document.getElementById('cart-view').style.display = 'grid';
      document.getElementById('checkout-view').style.display = 'none';
      document.getElementById('step-2').classList.remove('active');
    }

    // --- Sélecteur d'emplacement de livraison sur carte ---
    let deliveryMap = null;
    let deliveryMarker = null;
    let emplacementChoisi = null; // { lat, lng }

    function ouvrirCarte() {
      document.getElementById('map-modal-overlay').style.display = 'flex';

      if (! deliveryMap) {
        const centreDefaut = [36.8065, 10.1815]; // Tunis
        deliveryMap = L.map('delivery-map').setView(centreDefaut, 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '&copy; OpenStreetMap',
        }).addTo(deliveryMap);

        deliveryMap.on('click', (e) => placerRepere(e.latlng.lat, e.latlng.lng));

        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition((pos) => {
            if (! deliveryMarker) {
              deliveryMap.setView([pos.coords.latitude, pos.coords.longitude], 14);
            }
          }, () => {}, { timeout: 4000 });
        }
      }

      // Le modal était masqué lors de l'initialisation ; Leaflet a besoin d'un
      // recalcul de la taille une fois le conteneur visible.
      setTimeout(() => deliveryMap.invalidateSize(), 50);

      if (emplacementChoisi) {
        placerRepere(emplacementChoisi.lat, emplacementChoisi.lng, false);
      }
    }

    function fermerCarte() {
      document.getElementById('map-modal-overlay').style.display = 'none';
    }

    function placerRepere(lat, lng, recentrer = true) {
      if (deliveryMarker) {
        deliveryMarker.setLatLng([lat, lng]);
      } else {
        deliveryMarker = L.marker([lat, lng], { draggable: true }).addTo(deliveryMap);
        deliveryMarker.on('dragend', () => {
          const pos = deliveryMarker.getLatLng();
          placerRepere(pos.lat, pos.lng, false);
        });
      }

      if (recentrer) deliveryMap.setView([lat, lng], deliveryMap.getZoom() < 14 ? 15 : deliveryMap.getZoom());

      emplacementChoisi = { lat, lng };
      document.getElementById('map-confirm-btn').disabled = false;
      document.getElementById('map-selected-hint').textContent = `Repère placé — ${lat.toFixed(5)}, ${lng.toFixed(5)}`;

      rechercherAdresse(lat, lng);
    }

    let adresseTrouvee = null;

    function rechercherAdresse(lat, lng) {
      adresseTrouvee = null;

      fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
        .then((r) => r.json())
        .then((data) => {
          if (data && data.display_name) {
            adresseTrouvee = {
              texte: data.display_name,
              ville: data.address?.city || data.address?.town || data.address?.village || '',
              code_postal: data.address?.postcode || '',
            };
            if (emplacementChoisi && emplacementChoisi.lat === lat && emplacementChoisi.lng === lng) {
              document.getElementById('map-selected-hint').textContent = data.display_name;
            }
          }
        })
        .catch(() => {});
    }

    function confirmerEmplacement() {
      if (! emplacementChoisi) return;

      document.getElementById('latitude-input').value = emplacementChoisi.lat;
      document.getElementById('longitude-input').value = emplacementChoisi.lng;
      document.getElementById('adresse-input').value = adresseTrouvee?.texte || '';
      document.getElementById('ville-input').value = adresseTrouvee?.ville || '';

      const champCodePostal = document.getElementById('code_postal-input');
      if (! champCodePostal.value && adresseTrouvee?.code_postal) {
        champCodePostal.value = adresseTrouvee.code_postal;
      }

      document.getElementById('adresse-picker-text').textContent =
        adresseTrouvee?.texte || `Emplacement sélectionné (${emplacementChoisi.lat.toFixed(5)}, ${emplacementChoisi.lng.toFixed(5)})`;
      document.getElementById('adresse-picker').classList.add('chosen');

      fermerCarte();
    }

    function validerAdresseAvantEnvoi(e) {
      if (! document.getElementById('latitude-input').value || ! document.getElementById('longitude-input').value) {
        e.preventDefault();
        showToast("Choisis l'emplacement de livraison sur la carte avant de continuer.", 'error');
        ouvrirCarte();
        return false;
      }
      return true;
    }

    function changerQuantite(itemId, delta) {
      const span = document.getElementById(`item-qty-${itemId}`);
      const nouvelleQte = parseInt(span.textContent, 10) + delta;

      fetch(`/panier/${itemId}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': window.souqCsrfToken,
        },
        body: JSON.stringify({ quantite: nouvelleQte }),
      })
        .then((r) => r.json())
        .then((data) => {
          if (! data.success) { showToast("Une erreur est survenue.", 'error'); return; }
          updateCartBadge(data.panierCount);
          window.location.reload();
        })
        .catch(() => showToast("Une erreur est survenue.", 'error'));
    }

    function supprimerArticle(itemId) {
      fetch(`/panier/${itemId}`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': window.souqCsrfToken,
        },
      })
        .then((r) => r.json())
        .then((data) => {
          if (! data.success) { showToast("Une erreur est survenue.", 'error'); return; }
          updateCartBadge(data.panierCount);
          window.location.reload();
        })
        .catch(() => showToast("Une erreur est survenue.", 'error'));
    }
  </script>

</x-marketplace-layout>