<?php

namespace App\Http\Controllers;


use App\Models\Commande;
use App\Models\Favori;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the account overview page (avatar, stats, recent orders).
     */
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $commandes = Commande::where('user_id', $user->id)->get();
        $favori = Favori::where('user_id',$user->id)->get();
        $stats = [
            // "En cours" = actively being handled (waiting on the seller or in progress).
            'en_cours' => $commandes->whereIn('statut', ['en_attente', 'en_cours'])->count(),
            // "Livrées" = fully confirmed by both sides.
            'livrees' => $commandes->where('statut', 'confirmee')->count(),
            // Reviews and disputes aren't modeled yet, so there's nothing real to count.
            'favori' => $favori->count(),
            'litiges' => 0,
            
        ];

        $statusMap = [
            'en_attente' => ['slug' => 'attente', 'label' => 'En attente', 'action' => 'Suivre'],
            'en_cours' => ['slug' => 'preparation', 'label' => 'En préparation', 'action' => 'Suivre'],
            'confirmee' => ['slug' => 'livree', 'label' => 'Livrée', 'action' => 'Laisser un avis'],
            'annulee' => ['slug' => 'annulee', 'label' => 'Annulée', 'action' => 'Détails'],
        ];

        $recentOrders = Commande::where('user_id', $user->id)
            ->with(['boutique', 'items.produit.images'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function (Commande $commande) use ($statusMap) {
                $firstItem = $commande->items->first();
                $cover = $firstItem?->produit?->cover();
                $status = $statusMap[$commande->statut] ?? ['slug' => 'attente', 'label' => ucfirst($commande->statut), 'action' => 'Suivre'];

                return (object) [
                    'id' => $commande->id,
                    'produit_id' => $firstItem?->produit?->id,
                    'product_name' => $firstItem?->produit?->nom ?? 'Commande',
                    'reference' => $commande->reference,
                    'vendor_name' => $commande->boutique?->nom ?? '—',
                    'date_label' => $commande->created_at->translatedFormat('j M Y'),
                    'status_slug' => $status['slug'],
                    'status_label' => $status['label'],
                    'total' => $commande->total,
                    'action_label' => $status['action'],
                    'cover_url' => $cover?->url(),
                ];
            });

        return view('profile.client.edit', [
            'user' => $user,
            'stats' => $stats,
            'recentOrders' => $recentOrders,
        ]);
    }
}
