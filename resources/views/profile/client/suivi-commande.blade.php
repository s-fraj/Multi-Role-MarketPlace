<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<x-marketplace-layout title="Suivi de commande — Souq">

  @php
    $livraison = $commande->livraisonDrone;

    $etapes = [
      \App\Models\LivraisonDrone::STATUT_ATTENTE_DRONE => ['label' => "En attente d'un drone", 'icone' => 'ti-clock'],
      \App\Models\LivraisonDrone::STATUT_DECOLLAGE => ['label' => 'Décollage', 'icone' => 'ti-rocket'],
      \App\Models\LivraisonDrone::STATUT_VERS_RAMASSAGE => ['label' => 'En route vers le vendeur', 'icone' => 'ti-truck-delivery'],
      \App\Models\LivraisonDrone::STATUT_COLIS_RECUPERE => ['label' => 'Colis récupéré', 'icone' => 'ti-package'],
      \App\Models\LivraisonDrone::STATUT_VERS_CLIENT => ['label' => 'En route vers vous', 'icone' => 'ti-truck-delivery'],
      \App\Models\LivraisonDrone::STATUT_LIVRE => ['label' => 'Colis déposé', 'icone' => 'ti-map-pin-check'],
      \App\Models\LivraisonDrone::STATUT_TERMINE => ['label' => 'Livraison terminée', 'icone' => 'ti-circle-check'],
    ];
    $etapeIndexActuelle = $livraison?->etapeIndex();
  @endphp

  <div class="souq-app compte-layout">
    @include('profile.client.partials.sidebar', ['active' => 'commandes'])

    <main>
      <a href="{{ route('client.commandes') }}" style="font-size: 13px; color: var(--body-muted); text-decoration: none;"><i class="ti ti-arrow-left"></i> Mes commandes</a>
      <h1 class="page-title" style="margin-top: 10px;">Suivi de la commande {{ $commande->reference }}</h1>
      <div class="page-sub">{{ $commande->boutique->nom }} · {{ $commande->items->sum('quantite') }} article(s) · {{ number_format($commande->total, 2) }} DT</div>

      <div class="panel" style="margin-top: 20px;">
        <div class="panel-title">Statut de la livraison</div>

        @if (! $livraison)
          <div style="font-size: 13px; color: var(--body-muted); margin-top: 8px;">
            La livraison par drone n'a pas encore été lancée pour cette commande.
          </div>
        @else
          <div id="drone-badge-container">
            <div class="drone-badge {{ $livraison->estEnErreur() ? 'erreur' : ($livraison->estTerminee() ? 'terminee' : '') }}" id="drone-badge">
              <i class="ti {{ $livraison->estEnErreur() ? 'ti-alert-triangle' : 'ti-drone spin' }}" id="drone-badge-icon"></i>
              <span id="drone-badge-text">{{ $livraison->libelle() }}</span>
            </div>
          </div>

          <div class="drone-timeline" id="drone-timeline">
            @foreach ($etapes as $statutEtape => $infos)
              @php
                $indexEtape = array_search($statutEtape, array_keys($etapes));
                $classe = $etapeIndexActuelle === null ? '' : ($indexEtape < $etapeIndexActuelle ? 'fait' : ($indexEtape === $etapeIndexActuelle ? 'actif' : ''));
              @endphp
              <div class="drone-timeline-step {{ $classe }}" data-etape="{{ $indexEtape }}">
                <div class="drone-timeline-dot"><i class="ti {{ $infos['icone'] }}"></i></div>
                <div class="drone-timeline-label">{{ $infos['label'] }}</div>
              </div>
            @endforeach
          </div>

          <div id="drone-erreur-container">
            @if ($livraison->estEnErreur())
              <div class="drone-timeline-erreur">
                <i class="ti ti-alert-triangle"></i> Un problème est survenu pendant la livraison. Le vendeur a été notifié et va relancer l'envoi.
              </div>
            @endif
          </div>
        @endif
      </div>

      @if ($livraison)
        <div class="panel" style="margin-top: 20px;">
          <div class="panel-title">Trajet</div>
          <div id="suivi-map" style="height: 320px; border-radius: 10px; margin-top: 12px;"></div>
        </div>
      @endif
    </main>
  </div>

  @if ($livraison)
    <script>
      // --- Carte statique : point de ramassage (vendeur) + point de dépôt (client) ---
      const pickup = { lat: {{ $livraison->pickup_lat }}, lng: {{ $livraison->pickup_lng }} };
      const dropoff = { lat: {{ $livraison->dropoff_lat }}, lng: {{ $livraison->dropoff_lng }} };

      const suiviMap = L.map('suivi-map');
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap',
      }).addTo(suiviMap);

      const pickupIcon = L.divIcon({ className: '', html: '<div style="background:#C4552E; width:14px; height:14px; border-radius:50%; border:2px solid #fff;"></div>' });
      const dropoffIcon = L.divIcon({ className: '', html: '<div style="background:#1F7A4D; width:14px; height:14px; border-radius:50%; border:2px solid #fff;"></div>' });

      L.marker([pickup.lat, pickup.lng], { icon: pickupIcon }).addTo(suiviMap).bindPopup('Point de ramassage (vendeur)');
      L.marker([dropoff.lat, dropoff.lng], { icon: dropoffIcon }).addTo(suiviMap).bindPopup('Point de dépôt (vous)');

      suiviMap.fitBounds([[pickup.lat, pickup.lng], [dropoff.lat, dropoff.lng]], { padding: [40, 40] });

      // --- Statut en direct : polling toutes les 20 secondes ---
      const ETAPES_ORDRE = @json(array_keys($etapes));
      let sondagePausee = false;

      function rafraichirStatutDrone() {
        if (sondagePausee) return;

        fetch("{{ route('commandes.statut-drone', $commande) }}", { headers: { 'Accept': 'application/json' } })
          .then((r) => r.json())
          .then((data) => {
            if (! data.statut) return;

            document.getElementById('drone-badge-text').textContent = data.libelle;

            const badge = document.getElementById('drone-badge');
            const icon = document.getElementById('drone-badge-icon');
            badge.classList.remove('erreur', 'terminee');
            icon.classList.remove('ti-drone', 'spin', 'ti-alert-triangle', 'ti-circle-check');

            if (data.en_erreur) {
              badge.classList.add('erreur');
              icon.classList.add('ti-alert-triangle');
              document.getElementById('drone-erreur-container').innerHTML =
                '<div class="drone-timeline-erreur"><i class="ti ti-alert-triangle"></i> Un problème est survenu pendant la livraison. Le vendeur a été notifié et va relancer l\'envoi.</div>';
              sondagePausee = true;
            } else if (data.terminee) {
              badge.classList.add('terminee');
              icon.classList.add('ti-circle-check');
              sondagePausee = true;
            } else {
              icon.classList.add('ti-drone', 'spin');
            }

            if (data.etape_index !== null && data.etape_index !== undefined) {
              document.querySelectorAll('.drone-timeline-step').forEach((step) => {
                const idx = parseInt(step.dataset.etape, 10);
                step.classList.remove('fait', 'actif');
                if (idx < data.etape_index) step.classList.add('fait');
                else if (idx === data.etape_index) step.classList.add('actif');
              });
            }
          })
          .catch(() => {});
      }

      setInterval(rafraichirStatutDrone, 20000);
    </script>
  @endif

</x-marketplace-layout>
