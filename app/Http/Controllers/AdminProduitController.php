<?php

namespace App\Http\Controllers;

use App\Models\NotificationVendeur;
use App\Models\Produit;
use Illuminate\Http\Request;

class AdminProduitController extends Controller
{
    /**
     * GET /admin/produits — supervision de tous les produits, tous statuts confondus.
     */
    public function index(Request $request)
    {
        $produits = Produit::with(['boutique', 'images'])
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->query('statut')))
            ->when($request->filled('categorie'), fn ($q) => $q->where('categorie', $request->query('categorie')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.produits', [
            'produits' => $produits,
            'statutActif' => $request->query('statut'),
        ]);
    }

    /**
     * GET /admin/produits-en-attente — file de modération.
     */
    public function enAttente()
    {
        $produits = Produit::enAttente()
            ->with(['boutique', 'images'])
            ->oldest()
            ->get();

        return view('admin.produits-en-attente', [
            'produits' => $produits,
        ]);
    }

    /**
     * PATCH /admin/produits/{produit}/approuver
     */
    public function approuver(Produit $produit)
    {
        $produit->update(['statut' => Produit::STATUT_EN_LIGNE, 'raison_refus' => null]);

        NotificationVendeur::create([
            'user_id' => $produit->boutique->user_id,
            'produit_id' => $produit->id,
            'type' => NotificationVendeur::TYPE_PRODUIT_APPROUVE,
            'message' => "Ton produit \"{$produit->nom}\" a été approuvé et est maintenant en ligne.",
        ]);

        return response()->json([
            'success' => true,
            'message' => "Produit approuvé.",
        ]);
    }

    /**
     * PATCH /admin/produits/{produit}/refuser
     */
    public function refuser(Request $request, Produit $produit)
    {
        $data = $request->validate([
            'raison' => ['required', 'string', 'max:500'],
        ]);

        $produit->update(['statut' => Produit::STATUT_REFUSE, 'raison_refus' => $data['raison']]);

        NotificationVendeur::create([
            'user_id' => $produit->boutique->user_id,
            'produit_id' => $produit->id,
            'type' => NotificationVendeur::TYPE_PRODUIT_REFUSE,
            'message' => "Ton produit \"{$produit->nom}\" a été refusé : {$data['raison']}",
        ]);

        return response()->json([
            'success' => true,
            'message' => "Produit refusé.",
        ]);
    }
}
