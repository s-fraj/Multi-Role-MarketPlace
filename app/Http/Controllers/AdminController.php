<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
use App\Models\Produit;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'totalClients' => User::where('role', 'client')->count(),
            'totalVendeurs' => User::where('role', 'vendeur')->count(),
            'comptesBloques' => User::where('est_bloque', true)->count(),
            'totalProduitsEnLigne' => Produit::where('statut', Produit::STATUT_EN_LIGNE)->count(),
            'totalProduitsEnAttente' => Produit::where('statut', Produit::STATUT_EN_ATTENTE)->count(),
            'totalProduitsRefuses' => Produit::where('statut', Produit::STATUT_REFUSE)->count(),
            'boutiquesActives' => Boutique::where('statut', 'active')->count(),
            'boutiquesInactives' => Boutique::where('statut', 'inactive')->count(),
        ]);
    }
}
