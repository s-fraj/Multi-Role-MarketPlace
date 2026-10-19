<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProduitRequest;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProduitController extends Controller
{
    /**
     * /produits — catalogue publique, uniquement les produits publiés.
     * Filtrable par catégorie (?categorie=) et par recherche libre (?recherche=).
     */
    public function index(Request $request)
    {
        $categories = (array) $request->query('categories', []);
        if ($request->filled('categorie')) {
            $categories[] = $request->query('categorie');
        }
        $categories = array_values(array_unique(array_filter($categories)));

        $base = Produit::enLigne()
            ->whereHas('boutique', fn ($q) => $q->where('statut', 'active'))
            ->when($request->filled('recherche'), function ($q) use ($request) {
                $terme = $request->query('recherche');
                $q->where(function ($q2) use ($terme) {
                    $q2->where('nom', 'like', "%{$terme}%")
                        ->orWhere('description', 'like', "%{$terme}%");
                });
            })
            ->when($request->filled('prix_min'), function ($q) use ($request) {
                $q->where('prix', '>=', (float) $request->query('prix_min'));
            })
            ->when($request->filled('prix_max'), function ($q) use ($request) {
                $q->where('prix', '<=', (float) $request->query('prix_max'));
            })
            ->when($request->boolean('disponible'), function ($q) {
                $q->where('stock', '>', 0);
            });

        $comptesParCategorie = \App\Models\Categorie::active()
            ->withCount(['produits' => function ($q) use ($base) {
                $q->whereIn('id', (clone $base)->select('id'));
            }])
            ->pluck('produits_count', 'nom');

        $produits = (clone $base)
            ->with(['boutique', 'images', 'categorie'])
            ->when(count($categories) > 0, function ($query) use ($categories) {
                $query->whereHas('categorie', fn ($q) => $q->whereIn('nom', $categories));
            })
            ->latest()
            ->get();

        return view('produits', [
            'produits' => $produits,
            'categoriesActives' => $categories,
            'categorieActive' => $categories[0] ?? null,
            'comptesParCategorie' => $comptesParCategorie,
            'rechercheActive' => $request->query('recherche'),
            'prixMin' => $request->query('prix_min'),
            'prixMax' => $request->query('prix_max'),
            'disponibleActif' => $request->boolean('disponible'),
            'favorisIds' => $this->favorisIds(),
        ]);
    }


    /**
     * GET /produits/suggestions — utilisé par la barre de recherche du header
     * pour l'auto-complétion : renvoie les produits dont le nom contient la
     * séquence tapée, mis à jour à chaque lettre (voir header-search.js).
     */
    public function suggestions(Request $request)
    {
        $terme = trim((string) $request->query('q', ''));

        if ($terme === '') {
            return response()->json([]);
        }

        $suggestions = Produit::enLigne()
            ->whereHas('boutique', fn ($q) => $q->where('statut', 'active'))
            ->where('nom', 'like', "%{$terme}%")
            ->with('boutique')
            ->orderByRaw('LENGTH(nom) asc')
            ->limit(8)
            ->get()
            ->map(fn ($produit) => [
                'id' => $produit->id,
                'nom' => $produit->nom,
                'boutique' => $produit->boutique->nom ?? null,
                'prix' => (float) $produit->prix,
                'url' => route('produit-detail.index', $produit),
            ]);

        return response()->json($suggestions);
    }

    /**
     * /produit-detail/{produit} — fiche produit, alimentée par les infos
     * exactes saisies par le vendeur au moment de l'ajout.
     */
    public function show(Produit $produit)
    {
        $produit->load(['boutique', 'images', 'avis.user', 'avis.photos']);

        $estProprietaire = Auth::check() && Auth::user()->boutique?->id === $produit->boutique_id;
        $boutiqueIndisponible = $produit->boutique->statut !== 'active' && ! $estProprietaire;

        $user = Auth::user();

        return view('produit-detail', [
            'produit' => $produit,
            'favorisIds' => $this->favorisIds(),
            'peutNoter' => $produit->peutEtreNoteParClient($user),
            'monAvis' => $produit->avisDe($user),
            'boutiqueIndisponible' => $boutiqueIndisponible,
        ]);
    }

    /**
     * Ids des produits que l'utilisateur connecté a mis en favoris
     * (tableau vide pour un invité ou un vendeur).
     */
    private function favorisIds(): array
    {
        if (! Auth::check() || Auth::user()->isVendeur()) {
            return [];
        }

        return Auth::user()->favoris()->pluck('produit_id')->all();
    }

    /**
     * POST /vendeur/produits — ajout d'un produit par le vendeur connecté.
     * Le produit est enregistré puis visible immédiatement sur /produits
     * (si publié) et sur sa page produit-detail dédiée.
     */
    public function store(StoreProduitRequest $request)
    {
        $boutique = Auth::user()->boutique;

        if (! $boutique) {
            return back()->withErrors([
                'boutique' => "Vous devez d'abord créer votre boutique avant d'ajouter un produit.",
            ]);
        }

        $data = $request->validated();

        $variantes = [];
        foreach ($request->input('attributs', []) as $i => $attribut) {
            $nom = trim((string) ($attribut['nom'] ?? ''));
            if ($nom === '') {
                continue;
            }

            $valeurs = [];
            foreach ($attribut['valeurs'] ?? [] as $j => $valeur) {
                $libelle = trim((string) ($valeur['libelle'] ?? ''));
                if ($libelle === '') {
                    continue;
                }

                $photoPath = null;
                if ($request->hasFile("attributs.{$i}.valeurs.{$j}.photo")) {
                    $photoPath = $request->file("attributs.{$i}.valeurs.{$j}.photo")->store('produits/variantes', 'public');
                }

                $valeurs[] = [
                    'libelle' => $libelle,
                    'photo' => $photoPath,
                ];
            }

            if (! empty($valeurs)) {
                $variantes[] = ['nom' => $nom, 'valeurs' => $valeurs];
            }
        }

        $produit = $boutique->produits()->create([
            'nom' => $data['nom'],
            'slug' => Produit::slugFor($data['nom']),
            'description' => $data['description'] ?? null,
            'categorie_id' => \App\Models\Categorie::where('nom', $data['categorie'] ?? '')->value('id'),            'sku' => $data['sku'] ?? null,
            'prix' => $data['prix'],
            'prix_barre' => $data['prix_barre'] ?? null,
            'stock' => $data['stock'],
            'poids' => $data['poids'] ?? null,
            'variantes' => $variantes ?: null,
            'statut' => $data['statut'] === 'publier' ? Produit::STATUT_EN_ATTENTE : Produit::STATUT_BROUILLON,
        ]);

        foreach ($request->file('photos', []) as $i => $photo) {
            $path = $photo->store('produits', 'public');
            $produit->images()->create([
                'path' => $path,
                'ordre' => $i,
            ]);
        }

        if ($produit->statut === Produit::STATUT_EN_ATTENTE) {
            return redirect()
                ->route('vendeur.produits.index')
                ->with('success', 'Ton produit est en cours de vérification.');
        }

        return redirect()
            ->route('vendeur.produits.index')
            ->with('success', 'Produit enregistré comme brouillon.');
    }

    /**
     * GET /vendeur/produits — liste des produits du vendeur connecté, avec
     * leur statut de modération (brouillon / en attente / en ligne / refusé).
     */
    public function mesProduits()
    {
        $boutique = Auth::user()->boutique;

        $produits = $boutique
            ? $boutique->produits()->with('images')->latest()->get()
            : collect();

        return view('profile.vendeur.produits', [
            'produits' => $produits,
            
        ]);
    }


    /**
     * GET /vendeur/produits/{produit}/modifier — formulaire d'édition.
     */
    public function edit(Produit $produit)
    {
        if ($produit->boutique->user_id !== Auth::id()) {
            abort(403);
        }

        return view('profile.vendeur.vendeur-boutique', [
            'produit' => $produit->load('images'),
        ]);
    }

    /**
     * PATCH /vendeur/produits/{produit} — sauvegarde des modifications.
     * Repasse systématiquement en file de modération (en_attente).
     */
    public function update(Request $request, Produit $produit)
    {
        if ($produit->boutique->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'categorie' => ['nullable', 'string'],
            'sku' => ['nullable', 'string'],
            'prix' => ['required', 'numeric', 'min:0'],
            'prix_barre' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'poids' => ['nullable', 'numeric', 'min:0'],
        ]);

        $oldVariantes = $produit->variantes ?? [];

        $variantes = [];
        foreach ($request->input('attributs', []) as $i => $attribut) {
            $nom = trim((string) ($attribut['nom'] ?? ''));
            if ($nom === '') {
                continue;
            }

            $valeurs = [];
            foreach ($attribut['valeurs'] ?? [] as $j => $valeur) {
                $libelle = trim((string) ($valeur['libelle'] ?? ''));
                if ($libelle === '') {
                    continue;
                }

                $photoPath = null;
                if ($request->hasFile("attributs.{$i}.valeurs.{$j}.photo")) {
                    $photoPath = $request->file("attributs.{$i}.valeurs.{$j}.photo")->store('produits/variantes', 'public');
                } else {
                    // Conserve la photo existante si le libellé correspond
                    foreach ($oldVariantes as $oldVar) {
                        if (($oldVar['nom'] ?? '') === $nom) {
                            foreach ($oldVar['valeurs'] ?? [] as $oldVal) {
                                if (($oldVal['libelle'] ?? '') === $libelle && ! empty($oldVal['photo'])) {
                                    $photoPath = $oldVal['photo'];
                                    break 2;
                                }
                            }
                        }
                    }
                }

                $valeurs[] = ['libelle' => $libelle, 'photo' => $photoPath];
            }

            if (! empty($valeurs)) {
                $variantes[] = ['nom' => $nom, 'valeurs' => $valeurs];
            }
        }

        $produit->update([
            'nom' => $data['nom'],
            'slug' => Produit::slugFor($data['nom']),
            'description' => $data['description'] ?? null,
            'categorie_id' => \App\Models\Categorie::where('nom', $data['categorie'] ?? '')->value('id'),            'sku' => $data['sku'] ?? null,
            'prix' => $data['prix'],
            'prix_barre' => $data['prix_barre'] ?? null,
            'stock' => $data['stock'],
            'poids' => $data['poids'] ?? null,
            'variantes' => $variantes ?: null,
            'statut' => Produit::STATUT_EN_ATTENTE,
            'raison_refus' => null,
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos', []) as $i => $photo) {
                $path = $photo->store('produits', 'public');
                $produit->images()->create(['path' => $path, 'ordre' => $i]);
            }
        }

        return redirect()
            ->route('vendeur.produits.index')
            ->with('success', 'Modifications enregistrées — en attente de vérification.');
    }

    /**
     * DELETE /vendeur/produits/{produit} — suppression.
     */
    public function destroy(Produit $produit)
    {
        if ($produit->boutique->user_id !== Auth::id()) {
            abort(403);
        }

        $produit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produit supprimé.',
        ]);
    }















}
