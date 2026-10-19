<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<x-marketplace-layout title="Souk — Commandes">

  <div class="app">

    @include('profile.vendeur.partials.sidebar', ['active' => 'commandes'])

    <main class="main">
      <h1 class="page-title">Commandes</h1>
      <div class="page-sub">Gère les commandes de ta boutique</div>

      <div class="panel" style="padding: 0;">
        @forelse ($commandes as $commande)
          <div class="moderation-card">
            <div class="moderation-info" style="flex:1;">
              <div class="nom">Commande {{ $commande->reference }}</div>
              <div class="meta">
                {{ $commande->nom_client }} · {{ $commande->items->sum('quantite') }} article(s) ·
                {{ number_format($commande->total, 2) }} DT · {{ $commande->created_at->diffForHumans() }}
                @if ($commande->lienCarte())
                  · <a href="{{ $commande->lienCarte() }}" target="_blank" rel="noopener" style="color: var(--teal);"><i class="ti ti-map-pin"></i> Adresse de livraison</a>
                @endif
              </div>
              <div class="desc" style="margin-top:8px; font-size:13px;">
                @foreach ($commande->items as $item)
                  <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; padding:4px 0;">
                    <span>• {{ $item->quantite }}× {{ $item->produit->nom }}</span>
                    <a href="{{ route('produit-detail.index', $item->produit) }}" class="admin-btn admin-btn-outline" style="font-size:12px; padding:4px 10px;" target="_blank">Voir</a>
                  </div>
                @endforeach
              </div>
              @if ($commande->statut === 'en_attente')
                <div style="margin-top: 14px; padding: 14px; background: var(--paper); border-radius: 10px; border: 1px solid var(--line);">
                  <div style="font-size: 13px; font-weight: 600; color: var(--teal-deep); margin-bottom: 6px;">
                    <i class="ti ti-drone"></i> Livraison par drone
                  </div>
                  <p style="font-size: 13px; color: var(--body-muted); line-height: 1.6; margin-bottom: 12px;">
                    Prépare le colis, puis indique sur la carte l'endroit exact où le drone doit venir le récupérer. Une fois l'emplacement confirmé, un drone est assigné automatiquement (ou mis en attente si la flotte est occupée).
                  </p>

                  <button type="button" class="btn btn-primary" style="font-size: 13px; padding: 8px 16px;" onclick="afficherConfirmationLivraison({{ $commande->id }})">
                    <i class="ti ti-check"></i> Confirmer la livraison
                  </button>

                  <div id="livraison-panel-{{ $commande->id }}" style="display:none; margin-top: 14px;">
                    <form method="POST" action="{{ route('vendeur.commandes.livraison', $commande) }}" onsubmit="return validerLocationVendeurAvantEnvoi(event, {{ $commande->id }});">
                      @csrf
                      @method('PATCH')
                      <input type="hidden" name="latitude_vendeur" id="latitude-vendeur-input-{{ $commande->id }}" />
                      <input type="hidden" name="longitude_vendeur" id="longitude-vendeur-input-{{ $commande->id }}" />
                      <input type="hidden" name="adresse_vendeur" id="adresse-vendeur-input-{{ $commande->id }}" />

                      <div class="field" style="margin-bottom: 12px;">
                        <label>Adresse de livraison</label>
                        <div id="adresse-picker-{{ $commande->id }}" class="address-picker" onclick="ouvrirCarteVendeur({{ $commande->id }})">
                          <i class="ti ti-map-pin"></i>
                          <span id="adresse-picker-text-{{ $commande->id }}">Cliquez pour choisir l'emplacement sur la carte</span>
                        </div>
                      </div>

                      <div style="display:flex; gap:10px;">
                        <button type="submit" class="btn btn-primary" id="confirm-location-btn-{{ $commande->id }}" disabled style="font-size: 13px; padding: 8px 16px;">
                          <i class="ti ti-map-pin-check"></i> Confirmer la location
                        </button>
                        <button type="button" class="btn btn-ghost" style="font-size: 13px; padding: 8px 16px;" onclick="masquerConfirmationLivraison({{ $commande->id }})">Annuler</button>
                      </div>
                    </form>
                  </div>
                </div>
              @elseif ($commande->statut === 'en_cours')
                @php $livraison = $commande->livraisonDrone; @endphp
                <div style="margin-top: 10px;">
                @if ($livraison)
                  <div class="drone-badge {{ $livraison->estEnErreur() ? 'erreur' : ($livraison->estTerminee() ? 'terminee' : '') }}" id="drone-badge-{{ $commande->id }}" data-commande-id="{{ $commande->id }}">
                    <i class="ti {{ $livraison->estEnErreur() ? 'ti-alert-triangle' : 'ti-drone spin' }}" id="drone-badge-icon-{{ $commande->id }}"></i>
                    <span id="drone-badge-text-{{ $commande->id }}">{{ $livraison->libelle() }}</span>
                  </div>

                  @if ($livraison->estEnErreur())
                    <form method="POST" action="{{ route('commandes.drone.reessayer', $commande) }}" style="margin-top:10px;">
                      @csrf
                      <button type="submit" class="btn btn-primary" style="font-size:13px; padding: 8px 16px;">
                        <i class="ti ti-refresh"></i> Réessayer l'envoi au drone
                      </button>
                    </form>
                  @endif
                @else
                  <div style="font-size: 13px; color: #0C447C; background: #E6F1FB; padding: 8px 12px; border-radius: 8px; display:inline-block;">
                    <i class="ti ti-truck"></i> En cours de livraison
                  </div>
                @endif
                  @if ($commande->lienCarteVendeur())
                    <a href="{{ $commande->lienCarteVendeur() }}" target="_blank" rel="noopener" style="font-size: 12px; color: #0C447C; text-decoration: underline; margin-left: 8px;">Voir la location confirmée</a>
                  @endif
                </div>
              @elseif ($commande->statut === 'confirmee')
                <div style="margin-top: 10px; font-size: 13px; color: #0F6E56; background: #E1F5EE; padding: 8px 12px; border-radius: 8px; display:inline-block;">
                  <i class="ti ti-circle-check"></i> Commande finalisée
                </div>
              @elseif ($commande->statut === 'annulee')
                <div style="margin-top: 10px; font-size: 13px; color: var(--terracotta); background: #FADCD8; padding: 8px 12px; border-radius: 8px; display:inline-block;">
                  <i class="ti ti-alert-circle"></i> Annulée : {{ $commande->raison_annulation }}
                </div>
              @endif
            </div>
          </div>
        @empty
          <div class="empty-state">
            <i class="ti ti-shopping-bag"></i>
            Aucune commande pour le moment.
          </div>
        @endforelse
      </div>
    </main>
  </div>

  {{-- MAP PICKER MODAL --}}
  <div id="map-modal-overlay-vendeur" class="map-modal-overlay" style="display:none;">
    <div class="map-modal">
      <div class="map-modal-header">
        <div class="panel-title" style="margin:0;">Choisis l'emplacement de livraison</div>
        <span class="map-modal-close" onclick="fermerCarteVendeur()"><i class="ti ti-x"></i></span>
      </div>
      <div class="map-modal-hint"><i class="ti ti-info-circle"></i> Clique sur la carte (ou déplace le repère) pour placer le point de livraison.</div>
      <div id="delivery-map-vendeur"></div>
      <div class="map-modal-footer">
        <span id="map-selected-hint-vendeur" class="map-selected-hint">Aucun emplacement choisi</span>
        <div style="display:flex; gap:10px;">
          <button type="button" class="btn btn-ghost" onclick="fermerCarteVendeur()">Annuler</button>
          <button type="button" class="btn btn-primary" id="map-confirm-btn-vendeur" disabled onclick="confirmerEmplacementVendeur()">Confirmer l'emplacement</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    function afficherConfirmationLivraison(commandeId) {
      document.getElementById(`livraison-panel-${commandeId}`).style.display = 'block';
    }
    function masquerConfirmationLivraison(commandeId) {
      document.getElementById(`livraison-panel-${commandeId}`).style.display = 'none';
    }

    // --- Sélecteur d'emplacement de livraison sur carte (vendeur) ---
    let deliveryMapVendeur = null;
    let deliveryMarkerVendeur = null;
    let emplacementChoisiVendeur = null; // { lat, lng }
    let adresseTrouveeVendeur = null;
    let commandeIdActive = null;

    function ouvrirCarteVendeur(commandeId) {
      commandeIdActive = commandeId;
      document.getElementById('map-modal-overlay-vendeur').style.display = 'flex';

      if (! deliveryMapVendeur) {
        const centreDefaut = [36.8065, 10.1815]; // Tunis
        deliveryMapVendeur = L.map('delivery-map-vendeur').setView(centreDefaut, 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '&copy; OpenStreetMap',
        }).addTo(deliveryMapVendeur);

        deliveryMapVendeur.on('click', (e) => placerRepereVendeur(e.latlng.lat, e.latlng.lng));

        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition((pos) => {
            if (! deliveryMarkerVendeur) {
              deliveryMapVendeur.setView([pos.coords.latitude, pos.coords.longitude], 14);
            }
          }, () => {}, { timeout: 4000 });
        }
      }

      setTimeout(() => deliveryMapVendeur.invalidateSize(), 50);

      // Reprend un emplacement déjà choisi pour cette commande, s'il y en a un.
      const latInput = document.getElementById(`latitude-vendeur-input-${commandeId}`);
      const lngInput = document.getElementById(`longitude-vendeur-input-${commandeId}`);
      if (latInput.value && lngInput.value) {
        placerRepereVendeur(parseFloat(latInput.value), parseFloat(lngInput.value));
      } else if (emplacementChoisiVendeur) {
        placerRepereVendeur(emplacementChoisiVendeur.lat, emplacementChoisiVendeur.lng, false);
      }
    }

    function fermerCarteVendeur() {
      document.getElementById('map-modal-overlay-vendeur').style.display = 'none';
    }

    function placerRepereVendeur(lat, lng, recentrer = true) {
      if (deliveryMarkerVendeur) {
        deliveryMarkerVendeur.setLatLng([lat, lng]);
      } else {
        deliveryMarkerVendeur = L.marker([lat, lng], { draggable: true }).addTo(deliveryMapVendeur);
        deliveryMarkerVendeur.on('dragend', () => {
          const pos = deliveryMarkerVendeur.getLatLng();
          placerRepereVendeur(pos.lat, pos.lng, false);
        });
      }

      if (recentrer) deliveryMapVendeur.setView([lat, lng], deliveryMapVendeur.getZoom() < 14 ? 15 : deliveryMapVendeur.getZoom());

      emplacementChoisiVendeur = { lat, lng };
      document.getElementById('map-confirm-btn-vendeur').disabled = false;
      document.getElementById('map-selected-hint-vendeur').textContent = `Repère placé — ${lat.toFixed(5)}, ${lng.toFixed(5)}`;

      rechercherAdresseVendeur(lat, lng);
    }

    function rechercherAdresseVendeur(lat, lng) {
      adresseTrouveeVendeur = null;

      fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
        .then((r) => r.json())
        .then((data) => {
          if (data && data.display_name) {
            adresseTrouveeVendeur = data.display_name;
            if (emplacementChoisiVendeur && emplacementChoisiVendeur.lat === lat && emplacementChoisiVendeur.lng === lng) {
              document.getElementById('map-selected-hint-vendeur').textContent = data.display_name;
            }
          }
        })
        .catch(() => {});
    }

    function confirmerEmplacementVendeur() {
      if (! emplacementChoisiVendeur || ! commandeIdActive) return;

      document.getElementById(`latitude-vendeur-input-${commandeIdActive}`).value = emplacementChoisiVendeur.lat;
      document.getElementById(`longitude-vendeur-input-${commandeIdActive}`).value = emplacementChoisiVendeur.lng;
      document.getElementById(`adresse-vendeur-input-${commandeIdActive}`).value = adresseTrouveeVendeur || '';

      const texte = adresseTrouveeVendeur || `Emplacement sélectionné (${emplacementChoisiVendeur.lat.toFixed(5)}, ${emplacementChoisiVendeur.lng.toFixed(5)})`;
      document.getElementById(`adresse-picker-text-${commandeIdActive}`).textContent = texte;
      document.getElementById(`adresse-picker-${commandeIdActive}`).classList.add('chosen');
      document.getElementById(`confirm-location-btn-${commandeIdActive}`).disabled = false;

      fermerCarteVendeur();
    }

    function validerLocationVendeurAvantEnvoi(e, commandeId) {
      const latInput = document.getElementById(`latitude-vendeur-input-${commandeId}`);
      const lngInput = document.getElementById(`longitude-vendeur-input-${commandeId}`);
      if (! latInput.value || ! lngInput.value) {
        e.preventDefault();
        showToast("Choisis l'emplacement de livraison sur la carte avant de confirmer.", 'error');
        ouvrirCarteVendeur(commandeId);
        return false;
      }
      return true;
    }

    // --- Statuts drone en direct sur la liste des commandes ---
    const statutDroneUrlTemplate = "{{ route('commandes.statut-drone', ['commande' => '__ID__']) }}";
    const commandesEnSuivi = new Set();

    document.querySelectorAll('[id^="drone-badge-"]').forEach((badge) => {
      const commandeId = badge.dataset.commandeId;
      if (commandeId) commandesEnSuivi.add(commandeId);
    });

    function rafraichirBadgesDrone() {
      commandesEnSuivi.forEach((commandeId) => {
        const badge = document.getElementById(`drone-badge-${commandeId}`);
        if (! badge) return;

        fetch(statutDroneUrlTemplate.replace('__ID__', commandeId), { headers: { 'Accept': 'application/json' } })
          .then((r) => r.json())
          .then((data) => {
            if (! data.statut) return;

            document.getElementById(`drone-badge-text-${commandeId}`).textContent = data.libelle;

            const icon = document.getElementById(`drone-badge-icon-${commandeId}`);
            badge.classList.remove('erreur', 'terminee');
            icon.classList.remove('ti-drone', 'spin', 'ti-alert-triangle', 'ti-circle-check');

            if (data.en_erreur) {
              badge.classList.add('erreur');
              icon.classList.add('ti-alert-triangle');
              commandesEnSuivi.delete(commandeId);
            } else if (data.terminee) {
              badge.classList.add('terminee');
              icon.classList.add('ti-circle-check');
              commandesEnSuivi.delete(commandeId);
            } else {
              icon.classList.add('ti-drone', 'spin');
            }
          })
          .catch(() => {});
      });
    }

    if (commandesEnSuivi.size > 0) {
      setInterval(rafraichirBadgesDrone, 20000);
    }
  </script>

</x-marketplace-layout >