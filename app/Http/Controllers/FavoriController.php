<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Support\Facades\Auth;

class FavoriController extends Controller
{
    /**
     * /favoris — page favoris, réservée aux comptes client.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isVendeur()) {
            return redirect()
                ->route('produits.index')
                ->with('toast_error', "Vous n'avez pas cette option.");
        }

        $favoris = $user->favoris()
            ->with(['produit.boutique', 'produit.images'])
            ->latest()
            ->get();

        return view('profile.client.favoris', [
            'favoris' => $favoris,
        ]);
    }

    /**
     * POST /favoris/toggle/{produit} — appelé en AJAX depuis le cœur affiché
     * sur les cartes produit et la fiche produit. Client uniquement.
     */
    public function toggle(Produit $produit)
    {
        $user = Auth::user();

        if ($user->isVendeur()) {
            return response()->json([
                'success' => false,
                'message' => "Vous n'avez pas cette option.",
            ], 403);
        }

        $favori = $user->favoris()->where('produit_id', $produit->id)->first();

        if ($favori) {
            $favori->delete();
            $enFavori = false;
            $message = 'Retiré des favoris.';
        } else {
            $user->favoris()->create(['produit_id' => $produit->id]);
            $enFavori = true;
            $message = 'Ajouté aux favoris.';
        }

        return response()->json([
            'success' => true,
            'favori' => $enFavori,
            'message' => $message,
        ]);
    }
}
