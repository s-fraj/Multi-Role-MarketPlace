<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\CommandeItem;
use App\Models\LivraisonDrone;
use App\Models\NotificationClient;
use App\Models\NotificationVendeur;
use App\Services\DroneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommandeController extends Controller
{
    /**
     * POST /commandes — checkout depuis le panier.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->isVendeur()) {
            return redirect()->route('produits.index')->with('toast_error', "Vous n'avez pas cette option.");
        }

        $panier = $user->panier;
        if (! $panier || $panier->items->isEmpty()) {
            return redirect()->route('panier.index')->with('toast_error', 'Votre panier est vide.');
        }

        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:30'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'ville' => ['nullable', 'string', 'max:100'],
            'code_postal' => ['nullable', 'string', 'max:10'],
        ]);

        $adresse = $data['adresse'] ?: sprintf('Position sélectionnée sur la carte (%.6f, %.6f)', $data['latitude'], $data['longitude']);

        $items = $panier->items()->with(['produit.boutique'])->get();
        $groupes = $items->groupBy(fn ($item) => $item->produit->boutique_id);

        DB::transaction(function () use ($groupes, $data, $user, $adresse) {
            foreach ($groupes as $boutiqueId => $boutiqueItems) {
                $boutique = $boutiqueItems->first()->produit->boutique;
                $sousTotal = $boutiqueItems->sum(fn ($item) => $item->quantite * $item->produit->prix);
                $fraisLivraison = 7;

                $commande = Commande::create([
                    'user_id' => $user->id,
                    'boutique_id' => $boutiqueId,
                    'reference' => 'SOQ-' . strtoupper(uniqid()) . '-' . substr(md5(rand()), 0, 4),
                    'total' => $sousTotal + $fraisLivraison,
                    'frais_livraison' => $fraisLivraison,
                    'statut' => 'en_attente',
                    'nom_client' => $data['nom'],
                    'telephone' => $data['telephone'],
                    'adresse' => $adresse,
                    'ville' => $data['ville'] ?? '',
                    'code_postal' => $data['code_postal'] ?? '',
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                ]);

                foreach ($boutiqueItems as $item) {
                    $commande->items()->create([
                        'produit_id' => $item->produit_id,
                        'quantite' => $item->quantite,
                        'prix_unitaire' => $item->produit->prix,
                    ]);

                    // Decrement stock
                    $item->produit->decrement('stock', $item->quantite);
                }

                $produitsNoms = $boutiqueItems->map(fn ($i) => $i->produit->nom)->implode(', ');
                NotificationVendeur::create([
                    'user_id' => $boutique->user_id,
                    'commande_id' => $commande->id,
                    'type' => 'nouvelle_commande',
                    'message' => "{$user->name} a acheté : {$produitsNoms}",
                ]);
            }
        });

        $panier->items()->delete();

        return redirect()->route('compte')->with('toast_success', 'Commande passée avec succès. Les vendeurs ont été notifiés.');
    } 
 /**
     * PATCH /vendeur/commandes/{commande}/livraison
     */
    public function confirmerLivraison(Request $request, Commande $commande, DroneService $droneService)
    {
        if ($commande->boutique->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'latitude_vendeur' => ['required', 'numeric', 'between:-90,90'],
            'longitude_vendeur' => ['required', 'numeric', 'between:-180,180'],
            'adresse_vendeur' => ['nullable', 'string', 'max:255'],
        ]);

        $commande->update([
            'statut' => 'en_cours',
            'vendeur_confirme' => true,
            'vendeur_confirme_at' => now(),
            'latitude_vendeur' => $data['latitude_vendeur'],
            'longitude_vendeur' => $data['longitude_vendeur'],
            'adresse_vendeur' => $data['adresse_vendeur'] ?: sprintf('Position sélectionnée sur la carte (%.6f, %.6f)', $data['latitude_vendeur'], $data['longitude_vendeur']),
        ]);

        $produitsNoms = $commande->items->map(fn ($i) => $i->produit->nom)->implode(', ');

        NotificationClient::create([
            'user_id' => $commande->user_id,
            'commande_id' => $commande->id,
            'type' => 'livraison_confirme',
            'message' => "Votre commande de {$commande->boutique->nom} ({$produitsNoms}) a été expédiée. Confirmez la réception.",
        ]);

        $this->envoyerAuDrone($commande, $droneService, [
            'lat' => $data['latitude_vendeur'],
            'lng' => $data['longitude_vendeur'],
        ], [
            'lat' => (float) $commande->latitude,
            'lng' => (float) $commande->longitude,
        ]);

        return back()->with('toast_success', 'Livraison confirmée. Le client a été notifié.');
    }

    /**
     * Envoie la mission au service Python (pickup = position du vendeur,
     * dropoff = position du client) et enregistre le résultat dans
     * livraisons_drone. Ne bloque jamais la confirmation de livraison :
     * si le service drone est injoignable, la commande reste "en_cours"
     * normalement et l'échec est simplement noté pour être réessayé.
     */
    private function envoyerAuDrone(Commande $commande, DroneService $droneService, array $pickup, array $dropoff): void
    {
        try {
            $reponse = $droneService->demarrerLivraison($pickup, $dropoff, $commande->id);
            $mission = $reponse['mission'] ?? [];

            LivraisonDrone::updateOrCreate(
                ['commande_id' => $commande->id],
                [
                    'mission_id' => $mission['mission_id'] ?? null,
                    'drone_id' => $mission['drone_id'] ?? null,
                    'statut' => $mission['status'] ?? LivraisonDrone::STATUT_ATTENTE_DRONE,
                    'queue_position' => $mission['queue_position'] ?? null,
                    'erreur' => null,
                    'pickup_lat' => $pickup['lat'],
                    'pickup_lng' => $pickup['lng'],
                    'dropoff_lat' => $dropoff['lat'],
                    'dropoff_lng' => $dropoff['lng'],
                ]
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Échec de l\'envoi au service drone', [
                'commande_id' => $commande->id,
                'message' => $e->getMessage(),
            ]);

            LivraisonDrone::updateOrCreate(
                ['commande_id' => $commande->id],
                [
                    'mission_id' => null,
                    'drone_id' => null,
                    'statut' => LivraisonDrone::STATUT_ENVOI_IMPOSSIBLE,
                    'queue_position' => null,
                    'erreur' => $e->getMessage(),
                    'pickup_lat' => $pickup['lat'],
                    'pickup_lng' => $pickup['lng'],
                    'dropoff_lat' => $dropoff['lat'],
                    'dropoff_lng' => $dropoff['lng'],
                ]
            );
        }
    }

    /**
     * GET /vendeur/commandes — liste des commandes du vendeur.
     */
    public function vendeurIndex()
    {
        $commandes = Commande::with(['user', 'items.produit', 'livraisonDrone'])
            ->where('boutique_id', Auth::user()->boutique?->id)
            ->latest()
            ->get();

        return view('profile.vendeur.commandes', [
            'commandes' => $commandes,
        ]);
    }

    /**
     * GET /compte/commandes — liste des commandes du client.
     */
    public function clientIndex()
    {
        $commandes = Commande::with(['boutique', 'items.produit', 'livraisonDrone'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('profile.client.commandes', [
            'commandes' => $commandes,
        ]);
    }

    /**
     * GET /commandes/{commande}/suivi — page de suivi en direct de la
     * livraison par drone (carte + timeline mise à jour par polling).
     */
    public function suivi(Commande $commande)
    {
        if ($commande->user_id !== Auth::id()) {
            abort(403);
        }

        $commande->load(['boutique', 'items.produit', 'livraisonDrone']);

        return view('profile.client.suivi-commande', [
            'commande' => $commande,
        ]);
    }
}