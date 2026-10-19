<x-admin-layout title="Catégories — Admin" :active="'categories'">

    <h1 class="admin-page-title">Catégories</h1>
    <div class="admin-page-sub">Gérer les catégories du catalogue</div>

    {{-- Search + Add --}}
    <div style="display:flex; gap:12px; margin-bottom:24px; align-items:flex-end;" x-data="{ ouvert: false, nom: '' }">
        <form method="GET" action="{{ route('admin.categories') }}" style="flex:1; max-width:400px;">
            <div style="position:relative;">
                <i class="ti ti-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--muted);"></i>
                <input type="text" name="recherche" value="{{ $rechercheActive }}"
                       placeholder="Rechercher une catégorie..."
                       style="width:100%; padding:10px 14px 10px 38px; border:1px solid var(--line); border-radius:8px; font-family:inherit; font-size:14px;" />
            </div>
        </form>

        <button type="button" class="admin-btn admin-btn-accept" @click="ouvert = true">
            <i class="ti ti-plus"></i> Ajouter une catégorie
        </button>

        {{-- Add modal --}}
        <div x-show="ouvert" x-cloak style="position:fixed; inset:0; z-index:100; display:flex; align-items:center; justify-content:center; background:rgba(18,56,64,0.45);">
            <div style="background:var(--card); border:1px solid var(--line); border-radius:12px; padding:24px; width:360px;" @click.away="ouvert = false">
                <h3 style="font-family:'Fraunces',serif; font-size:18px; margin-bottom:16px;">Nouvelle catégorie</h3>
                <form method="POST" action="{{ route('admin.categories.store') }}">
                    @csrf
                    <input type="text" name="nom" x-model="nom" placeholder="Nom de la catégorie" required
                           style="width:100%; padding:10px 12px; border:1px solid var(--line); border-radius:8px; font-family:inherit; font-size:14px; margin-bottom:16px;" />
                    <div style="display:flex; gap:10px; justify-content:flex-end;">
                        <button type="button" class="admin-btn admin-btn-outline" @click="ouvert = false">Annuler</button>
                        <button type="submit" class="admin-btn admin-btn-accept">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="admin-panel">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Catégorie</th>
                    <th>Produits</th>
                    <th>Statut</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $categorie)
                    <tr id="cat-row-{{ $categorie->id }}">
                        <td>
                            <strong>{{ $categorie->nom }}</strong>
                            <div style="font-size:12px; color:var(--muted);">{{ $categorie->slug }}</div>
                        </td>
                        <td>{{ $categorie->produits_count }}</td>
                        <td>
                            <span class="statut-pill {{ $categorie->active ? 'actif' : 'inactive' }}" id="cat-statut-{{ $categorie->id }}">
                                {{ $categorie->active ? 'Active' : 'Désactivée' }}
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex; gap:6px; justify-content:flex-end;">
                                <button type="button" class="admin-btn admin-btn-outline" onclick="renommer({{ $categorie->id }}, '{{ $categorie->nom }}')">
                                    <i class="ti ti-pencil"></i>
                                </button>
                                <button type="button" class="admin-btn {{ $categorie->active ? 'admin-btn-refuse' : 'admin-btn-accept' }}" onclick="toggleCat({{ $categorie->id }})" id="cat-btn-{{ $categorie->id }}">
                                    <i class="ti {{ $categorie->active ? 'ti-eye-off' : 'ti-eye' }}"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="empty-state">Aucune catégorie trouvée.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        function renommer(id, nomActuel) {
            const nouveau = prompt('Nouveau nom de la catégorie :', nomActuel);
            if (! nouveau || nouveau === nomActuel) return;

            fetch(`/admin/categories/${id}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ nom: nouveau }),
            })
                .then(async (r) => {
                    const data = await r.json();
                    if (! r.ok) throw new Error(data.message || 'Erreur');
                    showToast('Catégorie renommée.', 'success');
                    window.location.reload();
                })
                .catch((err) => showToast(err.message || 'Erreur', 'error'));
        }

        function toggleCat(id) {
            fetch(`/admin/categories/${id}/toggle`, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            })
                .then(async (r) => {
                    const data = await r.json();
                    if (! r.ok) throw new Error(data.message || 'Erreur');

                    const badge = document.getElementById(`cat-statut-${id}`);
                    const btn = document.getElementById(`cat-btn-${id}`);
                    badge.textContent = data.active ? 'Active' : 'Désactivée';
                    badge.className = `statut-pill ${data.active ? 'actif' : 'inactive'}`;
                    btn.className = `admin-btn ${data.active ? 'admin-btn-refuse' : 'admin-btn-accept'}`;
                    btn.innerHTML = `<i class="ti ${data.active ? 'ti-eye-off' : 'ti-eye'}"></i>`;

                    showToast(data.message, 'success');
                })
                .catch((err) => showToast(err.message || 'Erreur', 'error'));
        }
    </script>

</x-admin-layout>