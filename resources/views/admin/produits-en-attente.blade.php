<x-admin-layout title="Produits en attente — Admin" :active="'produits-en-attente'">

    <h1 class="admin-page-title">Produits en attente</h1>
    <div class="admin-page-sub">{{ $produits->count() }} produit{{ $produits->count() > 1 ? 's' : '' }} en attente de validation</div>

    <div class="admin-panel">
        @forelse ($produits as $produit)
            <div class="moderation-card" id="moderation-{{ $produit->id }}">
                <div class="moderation-thumb" @if($produit->cover()) style="background-image:url('{{ $produit->cover()->url() }}');" @endif></div>
                <div class="moderation-info">
                    <div class="nom">{{ $produit->nom }}</div>
                    <div class="meta">
                        {{ $produit->boutique->nom }} · {{ $produit->categorie?->nom ?? 'Sans catégorie' }} ·
                        {{ number_format($produit->prix, 2) }} DT · Stock : {{ $produit->stock }} ·
                        Soumis {{ $produit->created_at->diffForHumans() }}
                    </div>
                    @if ($produit->description)
                        <div class="desc">{{ \Illuminate\Support\Str::limit($produit->description, 160) }}</div>
                    @endif
                </div>
                <div class="moderation-actions">
                    <button type="button" class="admin-btn admin-btn-accept" onclick="approuver({{ $produit->id }})"><i class="ti ti-check"></i> Accepter</button>
                    <button type="button" class="admin-btn admin-btn-refuse" onclick="ouvrirRefus({{ $produit->id }})"><i class="ti ti-x"></i> Refuser</button>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="ti ti-circle-check"></i>
                Aucun produit en attente — tout est traité.
            </div>
        @endforelse
    </div>

    <script>
        function retirerCarte(produitId) {
            const carte = document.getElementById(`moderation-${produitId}`);
            if (carte) carte.remove();
            decrementerBadge('badge-produits-en-attente');

            // Met aussi à jour le compteur affiché en haut de la page.
            const compteur = document.querySelector('.admin-page-sub');
            if (compteur) {
                const restants = document.querySelectorAll('.moderation-card').length;
                compteur.textContent = `${restants} produit${restants > 1 ? 's' : ''} en attente de validation`;
            }
        }

        function approuver(produitId) {
            envoyerAdmin(`/admin/produits/${produitId}/approuver`, 'PATCH')
                .then((data) => {
                    showToast(data.message, 'success');
                    retirerCarte(produitId);
                })
                .catch((err) => showToast(err.message, 'error'));
        }

        function ouvrirRefus(produitId) {
            const raison = prompt("Explique brièvement pourquoi ce produit est refusé (le vendeur verra ce message) :");
            if (! raison) return;

            envoyerAdmin(`/admin/produits/${produitId}/refuser`, 'PATCH', { raison })
                .then((data) => {
                    showToast(data.message, 'success');
                    retirerCarte(produitId);
                })
                .catch((err) => showToast(err.message, 'error'));
        }
    </script>

</x-admin-layout>
