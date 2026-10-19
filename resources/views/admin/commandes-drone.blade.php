<x-admin-layout title="Suivre commandes — Admin" :active="'commandes-drones'">

    <h1 class="admin-page-title">Suivre commandes</h1>
    <div class="admin-page-sub">Flotte de drones, file d'attente et livraisons en cours</div>

    <div class="admin-panel" style="padding: 20px;">
        <div class="admin-panel-title" style="margin-bottom: 14px;"><i class="ti ti-drone"></i> Flotte</div>

        @if ($flotteIndisponible)
            <div class="empty-state" style="padding: 24px;">
                <i class="ti ti-plug-connected-x"></i>
                Le service drone est actuellement injoignable. Les données ci-dessous peuvent être obsolètes.
            </div>
        @else
            <div class="flotte-grid">
                @foreach ($flotte['drones'] ?? [] as $droneId => $etat)
                    <div class="flotte-card {{ $etat['status'] === 'busy' ? 'busy' : '' }}">
                        <div class="flotte-card-title"><i class="ti ti-drone"></i> Drone {{ $droneId }}</div>
                        <div class="flotte-card-status">
                            @if ($etat['status'] === 'busy')
                                Occupé — commande liée à la mission {{ Str::limit($etat['mission_id'], 8, '…') }}
                            @else
                                Disponible
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if (($flotte['queue_length'] ?? 0) > 0)
                <div class="admin-panel-title" style="font-size: 14px; margin-bottom: 8px;">File d'attente ({{ $flotte['queue_length'] }})</div>
                <div class="file-attente">
                    @foreach ($flotte['queue'] ?? [] as $i => $missionId)
                        <span class="file-attente-item">{{ $i + 1 }}. {{ Str::limit($missionId, 8, '…') }}</span>
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    <div class="admin-panel">
        <div class="admin-panel-head">
            <div class="admin-panel-title"><i class="ti ti-list-check"></i> Livraisons drone actives</div>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Réf. commande</th>
                    <th>Vendeur</th>
                    <th>Client</th>
                    <th>Drone</th>
                    <th>Statut</th>
                    <th>Trajet</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($livraisons as $livraison)
                    @php $commande = $livraison->commande; @endphp
                    <tr>
                        <td><strong>{{ $commande->reference }}</strong></td>
                        <td>{{ $commande->boutique->nom }}</td>
                        <td>{{ $commande->user->name }}</td>
                        <td>{{ $livraison->drone_id ? "Drone {$livraison->drone_id}" : '—' }}</td>
                        <td>
                            <div class="drone-badge {{ $livraison->estEnErreur() ? 'erreur' : '' }}">
                                <i class="ti {{ $livraison->estEnErreur() ? 'ti-alert-triangle' : 'ti-drone spin' }}"></i>
                                {{ $livraison->libelle() }}
                            </div>
                            @if ($livraison->erreur)
                                <div style="font-size: 11px; color: var(--terracotta); margin-top: 4px;">{{ Str::limit($livraison->erreur, 80) }}</div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ $commande->lienCarteVendeur() }}" target="_blank" rel="noopener" style="font-size: 12px;">Ramassage</a>
                            ·
                            <a href="{{ $commande->lienCarte() }}" target="_blank" rel="noopener" style="font-size: 12px;">Dépôt</a>
                        </td>
                        <td>
                            @if ($livraison->estEnErreur())
                                <form method="POST" action="{{ route('admin.commandes.drone.reessayer', $commande) }}" onsubmit="return confirm('Réessayer l\'envoi de cette livraison au service drone ?');">
                                    @csrf
                                    <button type="submit" class="admin-btn admin-btn-accept" style="font-size:11px;">Réessayer</button>
                                </form>
                            @else
                                <span style="font-size:12px; color:var(--muted);">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state">Aucune livraison drone en cours.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-admin-layout>
