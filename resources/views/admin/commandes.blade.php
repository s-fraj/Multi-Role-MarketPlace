<x-admin-layout title="Commandes — Admin" :active="'commandes'">

    <h1 class="admin-page-title">Commandes</h1>
    <div class="admin-page-sub">Suivi et contrôle des commandes de la plateforme</div>

    <div class="admin-panel">
        <div class="admin-tabs">
            <a href="{{ route('admin.commandes') }}" class="@if($statutActif === 'en_attente' || !$statutActif) active @endif">
                En attente <span class="admin-nav-badge" style="position:static; margin-left:6px; display:inline-flex;">{{ $compteurs['en_attente'] }}</span>
            </a>
            <a href="{{ route('admin.commandes', ['statut' => 'en_cours']) }}" class="@if($statutActif === 'en_cours') active @endif">
                En cours <span class="admin-nav-badge" style="position:static; margin-left:6px; display:inline-flex;">{{ $compteurs['en_cours'] }}</span>
            </a>
            <a href="{{ route('admin.commandes', ['statut' => 'confirmee']) }}" class="@if($statutActif === 'confirmee') active @endif">
                Confirmées <span class="admin-nav-badge" style="position:static; margin-left:6px; display:inline-flex;">{{ $compteurs['confirmee'] }}</span>
            </a>
            <a href="{{ route('admin.commandes', ['statut' => 'annulee']) }}" class="@if($statutActif === 'annulee') active @endif">
                Annulées <span class="admin-nav-badge" style="position:static; margin-left:6px; display:inline-flex;">{{ $compteurs['annulee'] }}</span>
            </a>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Réf.</th>
                    <th>Client</th>
                    <th>Boutique</th>
                    <th>Produits</th>
                    <th>Total</th>
                    <th>Vendeur</th>
                    <th>Client</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($commandes as $commande)
                    <tr>
                        <td><strong>{{ $commande->reference }}</strong></td>
                        <td>
                            <div style="font-weight:600;">{{ $commande->nom_client }}</div>
                            <div style="font-size:12px; color:var(--muted);">{{ $commande->telephone }}</div>
                        </td>
                        <td>{{ $commande->boutique->nom }}</td>
                        <td>
                            @foreach ($commande->items as $item)
                                <div style="font-size:13px;">{{ $item->quantite }}× {{ $item->produit->nom }}</div>
                            @endforeach
                        </td>
                        <td>{{ number_format($commande->total, 2) }} DT</td>
                        <td>
                            @if ($commande->vendeur_confirme)
                                <span class="statut-pill actif" style="font-size:11px;">Confirmé</span>
                            @else
                                <span class="statut-pill statut-en-attente" style="font-size:11px;">Non confirmé</span>
                            @endif
                        </td>
                        <td>
                            @if ($commande->client_confirme)
                                <span class="statut-pill actif" style="font-size:11px;">Confirmé</span>
                            @else
                                <span class="statut-pill statut-en-attente" style="font-size:11px;">Non confirmé</span>
                            @endif
                        </td>
                        <td>
                            @if (!in_array($commande->statut, ['confirmee', 'annulee']))
                                <div style="display:flex; gap:6px;">
                                    <form method="POST" action="{{ route('admin.commandes.confirmer', $commande) }}" onsubmit="return confirm('Forcer la confirmation de cette commande ?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="admin-btn admin-btn-accept" style="font-size:11px;">Confirmer</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.commandes.annuler', $commande) }}" onsubmit="return confirm('Annuler cette commande ?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="admin-btn admin-btn-refuse" style="font-size:11px;">Annuler</button>
                                    </form>
                                </div>
                            @else
                                <span style="font-size:12px; color:var(--muted);">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="empty-state">Aucune commande dans cette catégorie.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $commandes->links() }}</div>

</x-admin-layout>