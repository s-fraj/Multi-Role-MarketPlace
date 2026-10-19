import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('produitSearch', () => ({
        terme: '',
        suggestions: [],
        ouvert: false,
        chargement: false,
        indexActif: -1,
        controleur: null,

        rechercher() {
            const q = this.terme.trim();

            if (this.controleur) {
                this.controleur.abort();
            }

            if (q.length === 0) {
                this.suggestions = [];
                this.ouvert = false;
                this.chargement = false;
                return;
            }

            this.controleur = new AbortController();
            this.chargement = true;
            this.ouvert = true;
            this.indexActif = -1;

            fetch(`/produits/suggestions?q=${encodeURIComponent(q)}`, { signal: this.controleur.signal })
                .then((reponse) => reponse.json())
                .then((data) => {
                    this.suggestions = data;
                    this.chargement = false;
                })
                .catch((err) => {
                    if (err.name !== 'AbortError') {
                        this.chargement = false;
                    }
                });
        },

        surligner(nom) {
            const q = this.terme.trim();
            if (! q) return nom;

            const idx = nom.toLowerCase().indexOf(q.toLowerCase());
            if (idx === -1) return nom;

            const avant = nom.slice(0, idx);
            const correspond = nom.slice(idx, idx + q.length);
            const apres = nom.slice(idx + q.length);

            return `${avant}<strong>${correspond}</strong>${apres}`;
        },

        deplacerSelection(pas) {
            if (! this.ouvert || this.suggestions.length === 0) return;
            const total = this.suggestions.length;
            this.indexActif = ((this.indexActif + pas) % total + total) % total;
        },

        validerSelection() {
            if (this.indexActif >= 0 && this.suggestions[this.indexActif]) {
                this.rechercherTerme(this.suggestions[this.indexActif].nom);
            }
        },

        rechercherTerme(nom) {
            window.location.href = `/produits?recherche=${encodeURIComponent(nom)}`;
        },
    }));
});

Alpine.start();