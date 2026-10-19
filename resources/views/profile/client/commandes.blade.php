<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

<x-marketplace-layout title="Mes commandes — Souq">

  <div class="souq-app compte-layout">
    @include('profile.client.partials.sidebar', ['active' => 'commandes'])

    <main>
      <h1 class="page-title">Mes commandes</h1>
      <div class="page-sub">Suivi de tes achats</div>

      <div class="panel" style="padding: 0; margin-top: 20px;">
        @forelse ($commandes as $commande)
          <div class="order-row" style="align-items: flex-start; padding: 20px;">
            <div class="order-info" style="flex:1;">
              <div class="order-name">Commande {{ $commande->reference }}</div>
              <div class="order-meta">
                {{ $commande->boutique->nom }} · {{ $commande->items->sum('quantite') }} article(s) ·
                {{ number_format($commande->total, 2) }} DT · {{ $commande->created_at->format('d/m/Y') }}
                @if ($commande->lienCarte())
                  · <a href="{{ $commande->lienCarte() }}" target="_blank" rel="noopener" style="color: var(--teal);"><i class="ti ti-map-pin"></i> Adresse de livraison</a>
                @endif
              </div>
              <div style="margin-top: 8px; font-size: 13px; color: var(--body-muted);">
                @foreach ($commande->items as $item)
                  <div>• {{ $item->quantite }}× {{ $item->produit->nom }}</div>
                @endforeach
              </div>

              @if ($commande->statut === 'en_attente')
                <span class="status-pill attente" style="margin-top: 12px;">En attente de préparation</span>
              @elseif ($commande->statut === 'en_cours')
                @php $livraison = $commande->livraisonDrone; @endphp
                <div style="margin-top: 14px; padding: 14px; background: var(--paper); border-radius: 10px; border: 1px solid var(--line);">
                  <div style="font-size: 13px; font-weight: 600; color: var(--teal-deep); margin-bottom: 6px;">
                    <i class="ti ti-drone"></i> Livraison par drone
                  </div>

                  @if ($livraison)
                    <div class="drone-badge {{ $livraison->estEnErreur() ? 'erreur' : ($livraison->estTerminee() ? 'terminee' : '') }}" style="margin-bottom: 12px;">
                      <i class="ti {{ $livraison->estEnErreur() ? 'ti-alert-triangle' : 'ti-drone spin' }}"></i>
                      {{ $livraison->libelle() }}
                    </div>
                  @endif

                  <div style="display:flex; gap:10px; flex-wrap: wrap;">
                    <a href="{{ route('commandes.suivi', $commande) }}" class="btn btn-ghost" style="font-size: 13px; padding: 8px 16px;">
                      <i class="ti ti-map-pin"></i> Suivre la commande
                    </a>
                  </div>
                </div>
              @elseif ($commande->statut === 'confirmee')
                <span class="status-pill livree" style="margin-top: 12px;"><i class="ti ti-circle-check"></i> Livrée par drone</span>
              @elseif ($commande->statut === 'annulee')
                <span class="status-pill litige" style="margin-top: 12px;"><i class="ti ti-alert-circle"></i> Annulée : {{ $commande->raison_annulation }}</span>
              @endif
            </div>
          </div>
        @empty
          <div class="panel-empty">
            Tu n'as pas encore de commande. <a href="{{ route('produits.index') }}">Découvrir les produits →</a>
          </div>
        @endforelse
      </div>
    </main>
  </div>

</x-marketplace-layout>