@php
    $user = Auth::user();
    $nameParts = collect(preg_split('/\s+/', trim($user->name)))->filter();
    $initials = $nameParts->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
    $prenom = $nameParts->first() ?? $user->name;
    $nomFamille = $nameParts->slice(1)->implode(' ');
    $prefs = $user->notificationsPreferences();
@endphp
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
<x-marketplace-layout title="Paramètres — Souq">

    <div class="souq-app compte-layout">
        @include('profile.client.partials.sidebar', ['active' => 'parametres'])

        <main>
            <h1 class="page-title">Paramètres</h1>
            <div class="page-sub">Gère tes informations personnelles, ta sécurité et tes préférences</div>

            <div class="panel">
                <div class="panel-title"><i class="ti ti-user"></i> Informations personnelles</div>

                <div class="avatar-row">
                    <div class="avatar-lg" data-avatar-display>
                        @if ($user->avatar)
                            <img src="{{ $user->avatarUrl() }}" alt="">
                        @else
                            {{ $initials }}
                        @endif
                    </div>
                    <div class="avatar-actions">
                        <button class="btn btn-primary" type="submit"  id="btn-changer-photo"><i class="ti ti-pencil"></i>Changer la photo</button>
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
                <div class="panel-title"><i class="ti ti-map-pin"></i> Adresse de livraison</div>

                <form id="form-adresse">
                    <div class="field">
                        <label>Adresse</label>
                        <input type="text" name="address" value="{{ $user->address }}" placeholder="Rue, numéro..." />
                    </div>
                    <div class="row-2">
                        <div class="field">
                            <label>Ville</label>
                            <input type="text" name="city" value="{{ $user->city }}" />
                        </div>
                        <div class="field">
                            <label>Code postal</label>
                            <input type="text" name="postal_code" value="{{ $user->postal_code }}" maxlength="10" />
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit">Enregistrer l'adresse</button>
                </form>
            </div>

            <div class="panel">
                <div class="panel-title"><i class="ti ti-lock"></i> Sécurité</div>

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
                <div class="panel-sub">Supprimer ton compte est définitif et efface ton panier et tes informations.</div>
                <button class="btn btn-danger" type="button" id="btn-supprimer-compte">Supprimer mon compte</button>
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

        document.getElementById('btn-changer-photo').addEventListener('click', function () {
            document.getElementById('input-photo-profil').click();
        });

        document.getElementById('input-photo-profil').addEventListener('change', function (e) {
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

        document.getElementById('form-profil').addEventListener('submit', function (e) {
            e.preventDefault();
            const form = e.target;
            envoyer("{{ route('parametres.profil') }}", 'PATCH', {
                prenom: form.prenom.value,
                nom: form.nom.value,
                email: form.email.value,
                phone: form.phone.value,
            })
                .then((data) => showToast(data.message, 'success'))
                .catch((err) => showToast(err.message, 'error'));
        });

        document.getElementById('form-adresse').addEventListener('submit', function (e) {
            e.preventDefault();
            const form = e.target;
            envoyer("{{ route('parametres.adresse') }}", 'PATCH', {
                address: form.address.value,
                city: form.city.value,
                postal_code: form.postal_code.value,
            })
                .then((data) => showToast(data.message, 'success'))
                .catch((err) => showToast(err.message, 'error'));
        });

        document.getElementById('form-mot-de-passe').addEventListener('submit', function (e) {
            e.preventDefault();
            const form = e.target;
            envoyer("{{ route('parametres.mot-de-passe') }}", 'PATCH', {
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

        document.querySelectorAll('.toggle[data-cle]').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                const activer = ! toggle.classList.contains('on');
                toggle.classList.toggle('on', activer);
                envoyer("{{ route('parametres.notifications') }}", 'PATCH', {
                    cle: toggle.dataset.cle,
                    valeur: activer,
                }).catch((err) => {
                    toggle.classList.toggle('on', ! activer);
                    showToast(err.message, 'error');
                });
            });
        });

        document.getElementById('btn-supprimer-compte').addEventListener('click', function () {
            if (! confirm("Cette action est définitive et supprime ton compte. Continuer ?")) return;
            const motDePasse = prompt("Confirme ton mot de passe pour supprimer définitivement ton compte :");
            if (! motDePasse) return;

            envoyer("{{ route('parametres.compte.supprimer') }}", 'DELETE', { mot_de_passe: motDePasse })
                .then((data) => { window.location.href = data.redirect; })
                .catch((err) => showToast(err.message, 'error'));
        });
    </script>

</x-marketplace-layout>
