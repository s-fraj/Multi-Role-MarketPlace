<script>
  (function () {
    // --- Avis : étoiles cliquables (fonctionne pour N widgets sur la page) ---
    document.querySelectorAll('.star-input').forEach(function (container) {
      const suffix = container.id.replace('star-input-', '');
      const hidden = document.getElementById('note-input-' + suffix);
      const stars = Array.from(container.querySelectorAll('i'));

      function peindre(jusqua) {
        stars.forEach(function (star) {
          const valeur = parseInt(star.dataset.valeur, 10);
          star.classList.toggle('filled', valeur <= jusqua);
        });
      }

      stars.forEach(function (star) {
        star.addEventListener('mouseenter', function () { peindre(parseInt(star.dataset.valeur, 10)); });
        star.addEventListener('click', function () {
          hidden.value = star.dataset.valeur;
          peindre(parseInt(star.dataset.valeur, 10));
        });
      });

      container.addEventListener('mouseleave', function () { peindre(parseInt(hidden.value, 10) || 0); });
    });

    // --- Avis : sélecteur de photos cumulatif (comme un "camera" Facebook) ---
    // Chaque clic sur l'icône appareil-photo AJOUTE des fichiers à la
    // sélection (au lieu de la remplacer), avec vignette + bouton retirer.
    // Un input caché "réel" (name="photos[]") est reconstruit à chaque
    // changement pour que le formulaire soumette bien tous les fichiers.
    function initPhotoPicker(suffix) {
      const pickerInput = document.getElementById('avis-photos-input-' + suffix);
      const realInput = document.getElementById('avis-photos-real-' + suffix);
      const row = document.getElementById('avis-photo-row-' + suffix);
      const camBtn = row.querySelector('.avis-camera-btn');
      let fichiers = [];

      function resynchroniser() {
        const dt = new DataTransfer();
        fichiers.forEach(function (f) { dt.items.add(f); });
        realInput.files = dt.files;
      }

      function rendre() {
        row.querySelectorAll('.avis-photo-slot.new-photo').forEach(function (el) { el.remove(); });

        fichiers.forEach(function (file, i) {
          const url = URL.createObjectURL(file);
          const slot = document.createElement('div');
          slot.className = 'avis-photo-slot new-photo';
          slot.style.backgroundImage = `url(${url})`;
          const remove = document.createElement('span');
          remove.className = 'avis-photo-remove';
          remove.innerHTML = '<i class="ti ti-x"></i>';
          remove.addEventListener('click', function () {
            fichiers.splice(i, 1);
            resynchroniser();
            rendre();
          });
          slot.appendChild(remove);
          row.insertBefore(slot, camBtn);
        });
      }

      pickerInput.addEventListener('change', function () {
        fichiers = fichiers.concat(Array.from(pickerInput.files));
        pickerInput.value = '';
        resynchroniser();
        rendre();
      });
    }

    // Auto-découverte : un picker par input dont l'id commence par
    // "avis-photos-input-" — fonctionne pour un seul formulaire (page
    // produit) comme pour N formulaires d'édition (page "Mes avis").
    document.querySelectorAll('[id^="avis-photos-input-"]').forEach(function (input) {
      initPhotoPicker(input.id.replace('avis-photos-input-', ''));
    });

    // --- Avis : retirer une photo déjà enregistrée ---
    // Le conteneur des inputs "photos_supprimees[]" est cherché à
    // l'intérieur du formulaire le plus proche, pas par un id fixe, pour
    // que ça reste correct même avec plusieurs formulaires d'édition.
    document.querySelectorAll('.avis-photo-slot.existing .avis-photo-remove').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const slot = btn.closest('.avis-photo-slot');
        const container = btn.closest('form').querySelector('.avis-photos-supprimees-container');
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'photos_supprimees[]';
        hidden.value = slot.dataset.photoId;
        container.appendChild(hidden);
        slot.remove();
      });
    });

    // --- Avis : basculer entre l'affichage d'un avis et son édition ---
    window.basculerEditionAvis = function (avisId) {
      const display = document.getElementById('avis-mine-display-' + avisId);
      const form = document.getElementById('avis-edit-form-' + avisId);
      const enEdition = form.style.display !== 'none';
      display.style.display = enEdition ? 'block' : 'none';
      form.style.display = enEdition ? 'none' : 'block';
    };
  })();
</script>
