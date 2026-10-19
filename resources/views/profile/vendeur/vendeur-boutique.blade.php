<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
<x-marketplace-layout title="Souk — Boutique">

  <div class="app">
    @include('profile.vendeur.partials.sidebar', ['active' => 'boutique'])

    <main class="main">
      <a href="{{ route('vendeur.produits.index') }}" class="back-link"><i class="ti ti-arrow-left"></i> Mes produits</a>

      @if ($errors->any())
        <div class="panel" style="border-color:#C4552E; margin-bottom:16px;">
          <div class="panel-title" style="color:#C4552E;">Merci de corriger les champs suivants</div>
          <ul style="margin:0; padding-left:18px; color:#C4552E; font-size:14px;">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ $produit ?? false ? route('vendeur.produits.update', $produit) : route('vendeur.produits.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if($produit ?? false)
          @method('PATCH')
        @else
          <input type="hidden" name="statut" id="statut-input" value="publier" />
        @endif

        <div class="top-row">
          <h1 class="page-title">{{ ($produit ?? false) ? 'Modifier le produit' : 'Ajouter un produit' }}</h1>
          <div class="header-actions">
            @if($produit ?? false)
              <a href="{{ route('vendeur.produits.index') }}" class="btn btn-ghost">Annuler</a>
              <button type="submit" class="btn btn-primary"><i class="ti ti-check"></i> Sauvegarder</button>
            @else
              <button type="submit" class="btn btn-ghost" onclick="document.getElementById('statut-input').value='brouillon';">Enregistrer comme brouillon</button>
              <button type="submit" class="btn btn-primary" onclick="document.getElementById('statut-input').value='publier';"><i class="ti ti-check"></i> Publier le produit</button>
            @endif
          </div>
        </div>

        <div class="layout">
          <div>
            <div class="panel">
              <div class="panel-title">Informations générales</div>
              <div class="field">
                <label>Nom du produit</label>
                <input type="text" name="nom" value="{{ old('nom', $produit?->nom) }}" placeholder="Ex: Théière en cuivre gravée" required />
              </div>
              <div class="field">
                <label>Description</label>
                <textarea name="description" placeholder="Décris le produit : matière, dimensions, origine, entretien...">{{ old('description', $produit?->description) }}</textarea>
              </div>
              <div class="row-2">
                <div class="field">
                  <label>Catégorie</label>
                  <select name="categorie" id="categorie-select">
                    @foreach (\App\Models\Categorie::active()->orderBy('nom')->pluck('nom') as $cat)                     
                      <option value="{{ $cat }}" @selected(old('categorie', $produit?->categorie) === $cat)>{{ $cat }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="field">
                  <label>Référence (SKU)</label>
                  <input type="text" name="sku" value="{{ old('sku', $produit?->sku) }}" placeholder="Ex: TH-0234" />
                </div>
              </div>
            </div>

            <div class="panel">
              <div class="panel-title">Photos</div>
              <div class="upload-grid" id="upload-grid">
                @if($produit ?? false)
                  @foreach($produit->images as $image)
                    <div class="upload-slot filled existing" style="background-image:url('{{ $image->url() }}'); background-size:cover; background-position:center;">
                      @if($loop->first)
                        <span class="cover-tag">Couverture</span>
                      @endif
                    </div>
                  @endforeach
                @endif
                <label class="upload-slot" id="upload-slot-add">
                  <i class="ti ti-photo-plus"></i>
                  Ajouter
                  <input type="file" name="photos[]" accept="image/*" multiple id="photos-input" style="display:none;" />
                </label>
              </div>
              <div class="hint">La première image est utilisée comme couverture dans le catalogue. 4 photos minimum recommandées.</div>
            </div>

            <div class="panel">
              <div class="panel-title">Prix et stock</div>
              <div class="row-3">
                <div class="field">
                  <label>Prix de vente</label>
                  <div class="input-suffix"><input type="number" step="0.01" min="0" name="prix" value="{{ old('prix', $produit?->prix) }}" placeholder="89" required /><span>DT</span></div>
                </div>
                <div class="field">
                  <label>Prix barré <span style="color: var(--muted); font-weight: 400;">(optionnel)</span></label>
                  <div class="input-suffix"><input type="number" step="0.01" min="0" name="prix_barre" value="{{ old('prix_barre', $produit?->prix_barre) }}" placeholder="110" /><span>DT</span></div>
                </div>
                <div class="field">
                  <label>Quantité en stock</label>
                  <input type="number" min="0" name="stock" value="{{ old('stock', $produit?->stock ?? 0) }}" placeholder="12" required />
                </div>
              </div>
            </div>

            <div class="panel">
              <div class="panel-title">Détails du produit <span style="color: var(--muted); font-weight: 400; font-size: 13px;">(optionnel)</span></div>

              <div class="suggestion-hint" id="suggestion-hint"></div>

              <div id="attribute-groups"></div>

              <div class="add-detail-btn" id="add-detail-btn"><i class="ti ti-plus"></i> Ajouter un détail</div>
            </div>
          </div>

          <div>
            @if(!($produit ?? false))
              <div class="panel">
                <div class="panel-title">Visibilité</div>
                <div class="toggle-row">
                  <div class="toggle-row-text">
                    Publier immédiatement
                    <small>Le produit sera visible dans le catalogue</small>
                  </div>
                  <span class="toggle on"></span>
                </div>
              </div>
            @else
              <div class="panel">
                <div class="panel-title">Statut</div>
                <div style="font-size:13px; color: var(--muted); padding: 8px 0;">
                  Ce produit sera remis en file de modération après sauvegarde.
                </div>
              </div>
            @endif

            <div class="panel">
              <div class="panel-title">Livraison</div>
              <div class="field" style="margin-bottom: 10px;">
                <label>Poids estimé</label>
                <div class="input-suffix"><input type="number" step="0.01" min="0" name="poids" value="{{ old('poids', $produit?->poids) }}" placeholder="0.8" /><span>kg</span></div>
              </div>
              <div class="hint">Utilisé pour estimer les frais de livraison First Delivery.</div>
            </div>
          </div>
        </div>
      </form>
    </main>
  </div>

  <script>
    // Live photo preview: each selected file gets its own thumbnail slot,
    // and the "Ajouter" slot always stays last.
    (function () {
      const grid = document.getElementById('upload-grid');
      const addSlot = document.getElementById('upload-slot-add');
      const input = document.getElementById('photos-input');
      let files = [];

      input.addEventListener('change', function () {
        files = Array.from(input.files || []);
        renderPreviews();
      });

      function renderPreviews() {
        grid.querySelectorAll('.upload-slot.filled.new-upload').forEach(el => el.remove());

        files.forEach((file, i) => {
          const url = URL.createObjectURL(file);
          const slot = document.createElement('div');
          slot.className = 'upload-slot filled new-upload';
          slot.style.backgroundImage = `url(${url})`;
          slot.style.backgroundSize = 'cover';
          slot.style.backgroundPosition = 'center';
          if (i === 0 && !document.querySelector('.upload-slot.filled.existing')) {
            const tag = document.createElement('span');
            tag.className = 'cover-tag';
            tag.textContent = 'Couverture';
            slot.appendChild(tag);
          }
          const remove = document.createElement('span');
          remove.className = 'remove-img';
          remove.innerHTML = '<i class="ti ti-x"></i>';
          remove.addEventListener('click', function (e) {
            e.preventDefault();
            files.splice(i, 1);
            syncInputFiles();
            renderPreviews();
          });
          slot.appendChild(remove);
          grid.insertBefore(slot, addSlot);
        });
      }

      function syncInputFiles() {
        const dt = new DataTransfer();
        files.forEach(f => dt.items.add(f));
        input.files = dt.files;
      }
    })();

    // Category-aware "detail" builder
    (function () {
      const suggestionsParCategorie = @json(\App\Models\Produit::attributsSuggeres());
      const requisParCategorie = { 'Mode & Textile': ['Taille'], 'Enfants': ['Taille'] };

      const groupsContainer = document.getElementById('attribute-groups');
      const hintContainer = document.getElementById('suggestion-hint');
      const categorieSelect = document.getElementById('categorie-select');
      let groupCounter = 0;

      function currentGroupNames() {
        return Array.from(groupsContainer.querySelectorAll('.attribute-name')).map(el => el.value.trim().toLowerCase());
      }

      function renderHint() {
        const suggestions = suggestionsParCategorie[categorieSelect.value] || [];
        const existing = currentGroupNames();
        const missing = suggestions.filter(s => ! existing.includes(s.toLowerCase()));

        if (missing.length === 0) {
          hintContainer.innerHTML = '';
          return;
        }

        hintContainer.innerHTML = `<span>Suggéré pour ${categorieSelect.value} :</span>` +
          missing.map(s => `<button type="button" class="suggestion-chip" data-nom="${s}"><i class="ti ti-plus"></i> ${s}</button>`).join('');

        hintContainer.querySelectorAll('.suggestion-chip').forEach(chip => {
          chip.addEventListener('click', function () {
            addAttributeGroup(chip.dataset.nom);
            renderHint();
          });
        });
      }

      function addValueRow(groupEl, groupIndex) {
        const valuesContainer = groupEl.querySelector('.attribute-values');
        const valueIndex = parseInt(groupEl.dataset.valueCounter || '0', 10);
        groupEl.dataset.valueCounter = valueIndex + 1;

        const row = document.createElement('div');
        row.className = 'value-row';
        row.innerHTML = `
          <label class="value-photo-upload" title="Photo (optionnel)">
            <i class="ti ti-camera-plus"></i>
            <input type="file" name="attributs[${groupIndex}][valeurs][${valueIndex}][photo]" accept="image/*" style="display:none;" />
          </label>
          <input type="text" name="attributs[${groupIndex}][valeurs][${valueIndex}][libelle]" placeholder="Ex: Rouge" class="value-libelle" />
          <i class="ti ti-x value-remove"></i>
        `;

        const photoInput = row.querySelector('input[type=file]');
        const photoLabel = row.querySelector('.value-photo-upload');
        photoInput.addEventListener('change', function () {
          if (photoInput.files && photoInput.files[0]) {
            photoLabel.style.backgroundImage = `url(${URL.createObjectURL(photoInput.files[0])})`;
            photoLabel.querySelector('i').style.display = 'none';
          }
        });

        row.querySelector('.value-remove').addEventListener('click', function () {
          row.remove();
        });

        valuesContainer.appendChild(row);
      }

      function addAttributeGroup(nomPreset = '') {
        const groupIndex = groupCounter++;
        const group = document.createElement('div');
        group.className = 'attribute-group';
        group.dataset.groupIndex = groupIndex;
        group.innerHTML = `
          <div class="attribute-head">
            <input type="text" name="attributs[${groupIndex}][nom]" placeholder="Nom du détail, ex: Couleur, Taille, Hauteur..." class="attribute-name" value="${nomPreset}" />
            <i class="ti ti-trash variant-remove"></i>
          </div>
          <div class="attribute-values"></div>
          <div class="add-value-btn"><i class="ti ti-plus"></i> Ajouter une valeur</div>
        `;

        group.querySelector('.variant-remove').addEventListener('click', function () {
          group.remove();
          renderHint();
        });
        group.querySelector('.attribute-name').addEventListener('input', renderHint);
        group.querySelector('.add-value-btn').addEventListener('click', function () {
          addValueRow(group, groupIndex);
        });

        groupsContainer.appendChild(group);
        addValueRow(group, groupIndex);

        return group;
      }

      document.getElementById('add-detail-btn').addEventListener('click', function () {
        addAttributeGroup();
      });

      categorieSelect.addEventListener('change', renderHint);
      renderHint();

      // Soft requirement: enforced on create (publier) and always on edit
      document.querySelector('form').addEventListener('submit', function (e) {
        const isEdit = !!document.querySelector('input[name="_method"][value="PATCH"]');
        if (! isEdit && document.getElementById('statut-input')?.value !== 'publier') return;

        const requis = requisParCategorie[categorieSelect.value] || [];
        const groupes = Array.from(groupsContainer.querySelectorAll('.attribute-group'));

        for (const nomRequis of requis) {
          const groupe = groupes.find(g => g.querySelector('.attribute-name').value.trim().toLowerCase() === nomRequis.toLowerCase());
          const rempli = groupe && Array.from(groupe.querySelectorAll('.value-libelle')).some(inp => inp.value.trim() !== '');

          if (! rempli) {
            e.preventDefault();
            showToast(`Merci d'indiquer "${nomRequis}" avant de publier ce produit.`, 'error');
            return;
          }
        }
      });

      // Pre-populate existing variantes when editing
      @if(($produit ?? false) && $produit->variantes)
        @foreach($produit->variantes as $variante)
          (function() {
            const group = addAttributeGroup(@json($variante['nom']));
            group.querySelector('.attribute-values').innerHTML = '';
            const gi = group.dataset.groupIndex;
            @foreach($variante['valeurs'] as $valeur)
              addValueRow(group, gi);
              const rows = group.querySelectorAll('.value-row');
              const lastRow = rows[rows.length - 1];
              lastRow.querySelector('.value-libelle').value = @json($valeur['libelle']);
              @if($valeur['photo'])
                lastRow.querySelector('.value-photo-upload').style.backgroundImage = `url({{ asset('storage/' . $valeur['photo']) }})`;
                lastRow.querySelector('.value-photo-upload i').style.display = 'none';
              @endif
            @endforeach
          })();
        @endforeach
      @endif
    })();
  </script>

</x-marketplace-layout >