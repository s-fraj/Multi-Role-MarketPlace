<?php

namespace App\Http\Controllers;

use App\Models\PanierItem;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanierController extends Controller
{
    /**
     * /panier — page panier, réservée aux comptes client.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isVendeur()) {
            return redirect()
                ->route('produits.index')
                ->with('toast_error', "Vous n'avez pas cette option.");
        }

        $panier = $user->panier;
        $items = $panier
            ? $panier->items()->with(['produit.boutique', 'produit.images'])->get()
            : collect();

        $groupes = $items->groupBy(fn ($item) => $item->produit->boutique->nom);

        $sousTotal = $items->sum(fn ($item) => $item->sousTotal());
        $livraison = $groupes->count() * 7;

        return view('panier', [
            'groupes' => $groupes,
            'sousTotal' => $sousTotal,
            'livraison' => $livraison,
            'total' => $sousTotal + $livraison,
        ]);
    }

    /**
     * POST /panier/ajouter/{produit} — appelé en AJAX depuis les boutons
     * "Ajouter au panier" / "Acheter maintenant". Client uniquement.
     */
    public function store(Request $request, Produit $produit)
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'redirect' => route('login'),
            ], 401);
        }

        $user = Auth::user();

        if ($user->isVendeur()) {
            return response()->json([
                'success' => false,
                'message' => "Vous n'avez pas cette option.",
            ], 403);
        }

        if ($produit->boutique->statut !== 'active' || $produit->statut !== 'en_ligne') {
            return response()->json([
                'success' => false,
                'message' => "Ce produit n'est plus disponible pour le moment.",
            ], 422);
        }

        $quantite = max(1, (int) $request->input('quantite', 1));

        $panier = $user->panier ?? $user->panier()->create();

        $item = $panier->items()->firstOrNew(['produit_id' => $produit->id]);
        $item->quantite = ($item->exists ? $item->quantite : 0) + $quantite;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Produit ajouté au panier.',
            'panierCount' => $panier->nombreArticles(),
        ]);
    }

    /**
     * PATCH /panier/{panierItem} — changer la quantité d'une ligne.
     */
    public function update(Request $request, PanierItem $panierItem)
    {
        if ($panierItem->panier->user_id !== Auth::id()) {
            abort(403);
        }

        $quantite = (int) $request->input('quantite', 1);

        if ($quantite < 1) {
            $panierItem->delete();
        } else {
            $panierItem->update(['quantite' => $quantite]);
        }

        $panier = $panierItem->panier;

        return response()->json([
            'success' => true,
            'panierCount' => $panier->nombreArticles(),
        ]);
    }

    /**
     * DELETE /panier/{panierItem} — retirer une ligne du panier.
     */
    public function destroy(PanierItem $panierItem)
    {
        if ($panierItem->panier->user_id !== Auth::id()) {
            abort(403);
        }

        $panier = $panierItem->panier;
        $panierItem->delete();

        return response()->json([
            'success' => true,
            'panierCount' => $panier->nombreArticles(),
        ]);
    }
}