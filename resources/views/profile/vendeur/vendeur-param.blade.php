@php
    $user = Auth::user();
    $nomParts = explode(' ', $user->name, 2);
    $prenom = $nomParts[0] ?? '';
    $nomFamille = $nomParts[1] ?? '';
    $initiales = strtoupper(substr($prenom, 0, 1).substr($nomFamille, 0, 1) ?: substr($user->name, 0, 2));
    $prefs = $user->notificationsPreferences();
    $moisFr = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    $membreDepuis = $boutique ? $moisFr[(int) $boutique->created_at->format('n')].' '.$boutique->created_at->format('Y') : '—';
@endphp
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
<x-marketplace-layout title="Souk — Paramètres vendeur">

<div class="app">
  @include('profile.vendeur.partials.sidebar', ['active' => 'parametres'])

  <main class="main">
    <h1 class="page-title">Mon profil</h1>
    <div class="page-sub">Gère tes informations personnelles, ta sécurité et tes préférences</div>

    <div class="layout">
      <div>
        <div class="panel">
          <div class="panel-title"><i class="ti ti-user"></i> Informations personnelles</div>
          <div class="panel-sub">Ces informations restent privées, à l'exception du nom de ta boutique.</div>

          <div class="avatar-row">
            <div class="avatar-lg" data-avatar-display>
                @if ($user->avatar)
                    <img src="{{ $user->avatarUrl() }}" alt="">
                @else
                    {{ $initiales }}
                @endif
            </div>
            <div class="avatar-actions">
              <button class="btn btn-primary" type="submit" id="btn-changer-photo"><i class="ti ti-pencil"></i>Changer la photo</button>
              <input type="file" id="input-photo-profil" accept="image/png, image/jpeg, image/webp" hidden>
            </div>
          </div>

          <form id="form-profil">
            <div class="row-2">
              <div class="field">
                <label>Prénom</label>
                <input type="text" name="prenom" value="{{ $prenom }}" required />
              </div>
              <div class="field">
                <label>Nom</label>
                <input type="text" name="nom" value="{{ $nomFamille }}" required />
              </div>
            </div>
            <div class="row-2">
              <div class="field">
                <label>Email</label>
                <input type="email" name="email" value="{{ $user->email }}" required />
              </div>
              <div class="field">
                <label>Téléphone</label>
                <input type="tel" name="phone" value="{{ $user->phone }}" />
              </div>
            </div>
            <button class="btn btn-primary" type="submit">Enregistrer les modifications</button>
          </form>
        </div>

        <div class="panel">
          <div class="panel-title"><i class="ti ti-lock"></i> Sécurité</div>
          <div class="panel-sub">Modifie ton mot de passe régulièrement pour protéger ton compte.</div>

          <form id="form-mot-de-passe">
            <div class="field">
              <label>Mot de passe actuel</label>
              <input type="password" name="mot_de_passe_actuel" placeholder="••••••••" required />
            </div>
            <div class="row-2">
              <div class="field">
                <label>Nouveau mot de passe</label>
                <input type="password" name="nouveau_mot_de_passe" placeholder="••••••••" required minlength="8" />
              </div>
              <div class="field">
                <label>Confirmer le mot de passe</label>
                <input type="password" name="nouveau_mot_de_passe_confirmation" placeholder="••••••••" required minlength="8" />
              </div>
            </div>
            <button class="btn btn-primary" type="submit">Mettre à jour le mot de passe</button>
          </form>
        </div>

        

        <div class="panel danger-panel">
          <div class="panel-title" style="color: var(--red);"><i class="ti ti-alert-triangle" style="color: var(--red);"></i> Zone sensible</div>
          <div class="panel-sub">Désactiver ta boutique la masque du catalogue sans supprimer tes données. La suppression est définitive.</div>
          <div style="display:flex; gap:10px;">
            <button class="btn btn-ghost" type="button" id="btn-toggle-boutique">
              {{ $boutique?->statut === 'active' ? 'Désactiver ma boutique' : 'Réactiver ma boutique' }}
            </button>
            <button class="btn btn-danger" type="button" id="btn-supprimer-compte">Supprimer mon compte</button>
          </div>
        </div>
      </div>

      <div>
        <div class="panel">
          <div class="panel-title" style="font-size:15px;"><i class="ti ti-building-store"></i> {{ $boutique->nom ?? 'Aucune boutique' }}</div>
          <div class="side-summary"><span>Statut</span><b id="side-statut">{{ $boutique?->statut === 'active' ? 'Active ✓' : 'Désactivée' }}</b></div>
          <div class="side-summary"><span>Membre depuis</span><b>{{ $membreDepuis }}</b></div>
          <div class="side-summary"><span>Produits actifs</span><b>{{ $boutique?->produits()->where('statut', 'en_ligne')->count() ?? 0 }}</b></div>
          <div class="side-summary"><span>Note moyenne</span><b>Pas encore d'avis</b></div>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  function envoyer(url, method, payload) {
    return fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify(payload),
    }).then(async (response) => {
      const data = await response.json().catch(() => ({}));
      if (! response.ok) {
        const premierMessage = data.errors ? Object.values(data.errors)[0][0] : (data.message || "Une erreur est survenue.");
        throw new Error(premierMessage);
      }
      return data;
    });
  }

  // Attache un écouteur seulement si l'élément existe, pour qu'un élément
  // manquant sur une variante de la page ne bloque pas tous les autres.
  function on(id, event, handler) {
    const el = document.getElementById(id);
    if (! el) {
      console.warn(`[parametres] élément #${id} introuvable — écouteur "${event}" non attaché.`);
      return;
    }
    el.addEventListener(event, handler);
  }

  on('btn-changer-photo', 'click', function () {
      document.getElementById('input-photo-profil').click();
  });

  on('input-photo-profil', 'change', function (e) {
      const input = e.target;
      if (! input.files || ! input.files[0]) return;

      const formData = new FormData();
      formData.append('photo', input.files[0]);

      fetch("{{ route('profile.photo.update') }}", {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
          body: formData,
      })
          .then(async (response) => {
              const data = await response.json().catch(() => ({}));
              if (! response.ok) throw new Error(data.message || "Une erreur est survenue.");

              document.querySelectorAll('[data-avatar-display]').forEach((el) => {
                  el.innerHTML = `<img src="${data.url}" alt="">`;
              });

              showToast(data.message, 'success');
          })
          .catch((err) => showToast(err.message, 'error'))
          .finally(() => { input.value = ''; });
  });

  on('form-profil', 'submit', function (e) {
    e.preventDefault();
    const form = e.target;
    envoyer("{{ route('vendeur.parametres.profil') }}", 'PATCH', {
      prenom: form.prenom.value,
      nom: form.nom.value,
      email: form.email.value,
      phone: form.phone.value,
    })
      .then((data) => showToast(data.message, 'success'))
      .catch((err) => showToast(err.message, 'error'));
  });

  on('form-mot-de-passe', 'submit', function (e) {
    e.preventDefault();
    const form = e.target;
    envoyer("{{ route('vendeur.parametres.mot-de-passe') }}", 'PATCH', {
      mot_de_passe_actuel: form.mot_de_passe_actuel.value,
      nouveau_mot_de_passe: form.nouveau_mot_de_passe.value,
      nouveau_mot_de_passe_confirmation: form.nouveau_mot_de_passe_confirmation.value,
    })
      .then((data) => {
        showToast(data.message, 'success');
        form.reset();
      })
      .catch((err) => showToast(err.message, 'error'));
  });

  on('form-banque', 'submit', function (e) {
    e.preventDefault();
    const form = e.target;
    envoyer("{{ route('vendeur.parametres.banque') }}", 'PATCH', {
      banque: form.banque.value,
      titulaire_compte: form.titulaire_compte.value,
      rib: form.rib.value,
    })
      .then((data) => {
        showToast(data.message, 'success');
        window.location.reload();
      })
      .catch((err) => showToast(err.message, 'error'));
  });

  document.querySelectorAll('.toggle[data-cle]').forEach(function (toggle) {
    toggle.addEventListener('click', function () {
      const activer = ! toggle.classList.contains('on');
      toggle.classList.toggle('on', activer);
      envoyer("{{ route('vendeur.parametres.notifications') }}", 'PATCH', {
        cle: toggle.dataset.cle,
        valeur: activer,
      }).catch((err) => {
        toggle.classList.toggle('on', ! activer);
        showToast(err.message, 'error');
      });
    });
  });

  on('btn-toggle-boutique', 'click', function () {
    const btn = this;
    const enCours = btn.textContent.trim().includes('Désactiver');
    if (! confirm(enCours
      ? "Désactiver ta boutique la masque immédiatement du catalogue. Continuer ?"
      : "Réactiver ta boutique la rend de nouveau visible dans le catalogue. Continuer ?")) {
      return;
    }

    envoyer("{{ route('vendeur.parametres.boutique-statut') }}", 'PATCH', {})
      .then((data) => {
        showToast(data.message, 'success');
        btn.textContent = data.statut === 'active' ? 'Désactiver ma boutique' : 'Réactiver ma boutique';
        document.getElementById('side-statut').textContent = data.statut === 'active' ? 'Active ✓' : 'Désactivée';
      })
      .catch((err) => showToast(err.message, 'error'));
  });

  on('btn-supprimer-compte', 'click', function () {
    if (! confirm("Cette action est définitive et supprime ta boutique, tes produits et ton compte. Continuer ?")) {
      return;
    }
    const motDePasse = prompt("Confirme ton mot de passe pour supprimer définitivement ton compte :");
    if (! motDePasse) return;

    envoyer("{{ route('vendeur.parametres.compte.supprimer') }}", 'DELETE', { mot_de_passe: motDePasse })
      .then((data) => { window.location.href = data.redirect; })
      .catch((err) => showToast(err.message, 'error'));
  });
</script>

</x-marketplace-layout >
