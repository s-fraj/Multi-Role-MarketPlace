<x-admin-layout title="Utilisateurs — Admin" :active="'utilisateurs'">

    <h1 class="admin-page-title">Utilisateurs</h1>
    <div class="admin-page-sub">Gère les comptes clients et vendeurs de la plateforme</div>

    <form method="GET" action="{{ route('admin.utilisateurs') }}" style="display:flex; gap:10px; margin-bottom:16px;">
        <input type="text" name="recherche" value="{{ $rechercheActive }}" placeholder="Nom ou email..." style="flex:1; max-width:320px; padding:9px 14px; border:1px solid var(--line); border-radius:8px; font-family:inherit; font-size:14px;" />
        <select name="role" onchange="this.form.submit()" style="padding:9px 14px; border:1px solid var(--line); border-radius:8px; font-family:inherit; font-size:14px;">
            <option value="">Tous les rôles</option>
            <option value="client" @selected($roleActif === 'client')>Client</option>
            <option value="vendeur" @selected($roleActif === 'vendeur')>Vendeur</option>
            <option value="admin" @selected($roleActif === 'admin')>Admin</option>
        </select>
        <button type="submit" class="admin-btn admin-btn-outline">Filtrer</button>
    </form>

    <div class="admin-panel">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Rôle</th>
                    <th>Boutique</th>
                    <th>Inscrit le</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($utilisateurs as $utilisateur)
                    <tr id="user-row-{{ $utilisateur->id }}">
                        <td>
                            <div style="font-weight:600;">{{ $utilisateur->name }}</div>
                            <div style="color: var(--muted); font-size:13px;">{{ $utilisateur->email }}</div>
                        </td>
                        <td><span class="role-badge {{ $utilisateur->role }}">{{ ucfirst($utilisateur->role) }}</span></td>
                        <td>{{ $utilisateur->boutique?->nom ?? '—' }}</td>
                        <td>{{ $utilisateur->created_at->format('d/m/Y') }}</td>
                        <td>
                            <span class="statut-pill {{ $utilisateur->est_bloque ? 'bloque' : 'actif' }}" id="user-statut-{{ $utilisateur->id }}">
                                {{ $utilisateur->est_bloque ? 'Bloqué' : 'Actif' }}
                            </span>
                        </td>
                        <td>
                            @unless ($utilisateur->isAdmin())
                                <button type="button" class="admin-btn admin-btn-outline @if($utilisateur->est_bloque === false) danger @endif"
                                        onclick="toggleBlocage({{ $utilisateur->id }}, this)">
                                    {{ $utilisateur->est_bloque ? 'Débloquer' : 'Bloquer' }}
                                </button>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state">Aucun utilisateur trouvé.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">{{ $utilisateurs->links() }}</div>

    <script>
        function toggleBlocage(userId, btn) {
            envoyerAdmin(`/admin/utilisateurs/${userId}/blocage`, 'PATCH')
                .then((data) => {
                    showToast(data.message, 'success');
                    document.getElementById(`user-statut-${userId}`).textContent = data.bloque ? 'Bloqué' : 'Actif';
                    document.getElementById(`user-statut-${userId}`).className = `statut-pill ${data.bloque ? 'bloque' : 'actif'}`;
                    btn.textContent = data.bloque ? 'Débloquer' : 'Bloquer';
                    btn.classList.toggle('danger', ! data.bloque);
                })
                .catch((err) => showToast(err.message, 'error'));
        }
    </script>

</x-admin-layout>
