<x-admin-layout title="Produits — Admin" :active="'produits'">

    <h1 class="admin-page-title">Produits</h1>
    <div class="admin-page-sub">Supervision de tous les produits de la plateforme</div>

    <div class="admin-panel">
        <div class="admin-tabs">
            <a href="{{ route('admin.produits') }}" class="@if(!$statutActif) active @endif">Tous</a>
            <a href="{{ route('admin.produits', ['statut' => 'en_ligne']) }}" class="@if($statutActif === 'en_ligne') active @endif">En ligne</a>
            <a href="{{ route('admin.produits', ['statut' => 'en_attente']) }}" class="@if($statutActif === 'en_attente') active @endif">En attente</a>
            <a href="{{ route('admin.produits', ['statut' => 'refuse']) }}" class="@if($statutActif === 'refuse') active @endif">Refusés</a>
            <a href="{{ route('admin.produits', ['statut' => 'brouillon']) }}" class="@if($statutActif === 'brouillon') active @endif">Brouillons</a>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Boutique</th>
                    <th>Catégorie</th>
                    <th>Prix</th>
                    <th>Statut</th>
                    <th>Ajouté le</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($produits as $produit)
                    @php $badge = $produit->statutBadge(); @endphp
                    <tr>
                        <td style="display:flex; align-items:center; gap:10px;">
                            <div style="width:36px; height:36px; border-radius:7px; background:#EDE7D6; flex-shrink:0; @if($produit->cover()) background-image:url('{{ $produit->cover()->url() }}'); background-size:cover; background-position:center; @endif"></div>
                            {{ $produit->nom }}
                        </td>
                        <td>{{ $produit->boutique->nom }}</td>
                        <td>{{$produit->categorie?->nom ?? '—' }}</td>
                        <td>{{ number_format($produit->prix, 2) }} DT</td>
                        <td><span class="statut-pill {{ $badge['classe'] }}">{{ $badge['texte'] }}</span></td>
                        <td>{{ $produit->created_at->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state">Aucun produit trouvé.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $produits->links() }}</div>

</x-admin-layout>
