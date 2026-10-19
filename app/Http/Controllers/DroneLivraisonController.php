<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\LivraisonDrone;
use App\Models\NotificationClient;
use App\Models\NotificationVendeur;
use App\Services\DroneService;
use Illuminate\Support\Facades\Auth;

class DroneLivraisonController extends Controller
{
    /**
     * GET /commandes/{commande}/statut-drone
     *
     * Polling endpoint shared by the client tracking page, the vendor
     * order list, and the admin fleet page. Pulls the latest status from
     * the Python service on every call (no queue/cron — the browser's
     * poll IS what triggers the Laravel -> Flask call), persists it if it
     * changed, and fires notifications on the meaningful transitions.
     */
    public function statut(Commande $commande, DroneService $droneService)
    {
        $this->autoriserAcces($commande);

        $livraison = $commande->livraisonDrone;

        if (! $livraison) {
            return response()->json(['statut' => null, 'libelle' => "Aucune livraison drone pour cette commande."]);
        }

        if ($livraison->mission_id && ! $livraison->estTerminee() && ! $livraison->estEnErreur()) {
            try {
                $mission = $droneService->statutMission($livraison->mission_id);
                $this->appliquerMiseAJour($commande, $livraison, $mission);
            } catch (\Throwable $e) {
                // Le service est momentanément injoignable : on renvoie le
                // dernier statut connu en base plutôt que de casser le
                // polling de la page.
            }
        }

        return response()->json([
            'statut' => $livraison->statut,
            'libelle' => $livraison->libelle(),
            'etape_index' => $livraison->etapeIndex(),
            'total_etapes' => count(LivraisonDrone::ETAPES),
            'en_erreur' => $livraison->estEnErreur(),
            'terminee' => $livraison->estTerminee(),
            'drone_id' => $livraison->drone_id,
            'queue_position' => $livraison->queue_position,
            'pickup' => ['lat' => $livraison->pickup_lat, 'lng' => $livraison->pickup_lng],
            'dropoff' => ['lat' => $livraison->dropoff_lat, 'lng' => $livraison->dropoff_lng],
        ]);
    }

    /**
     * GET /admin/commandes/drones — vue d'ensemble de la flotte + file
     * d'attente + toutes les livraisons drone en cours.
     */
    public function admin(DroneService $droneService)
    {
        $flotte = null;
        $flotteIndisponible = false;

        try {
            $flotte = $droneService->statutFlotte();
        } catch (\Throwable $e) {
            $flotteIndisponible = true;
        }

        $livraisons = LivraisonDrone::with(['commande.boutique', 'commande.user'])
            ->where('statut', '!=', LivraisonDrone::STATUT_TERMINE)
            ->latest()
            ->get();

        return view('admin.commandes-drone', [
            'flotte' => $flotte,
            'flotteIndisponible' => $flotteIndisponible,
            'livraisons' => $livraisons,
        ]);
    }

    /**
     * POST /admin/commandes/{commande}/drone/reessayer — relance l'envoi
     * au service drone pour une livraison en échec.
     */
    public function reessayer(Commande $commande, DroneService $droneService)
    {
        // Autorise le vendeur propriétaire OU l'admin
        $user = Auth::user();
        $estProprietaire = $commande->boutique->user_id === $user->id;
        abort_unless($estProprietaire || $user->isAdmin(), 403);

        $livraison = $commande->livraisonDrone;

        if (! $livraison) {
            return back()->with('toast_error', "Aucune livraison drone à réessayer pour cette commande.");
        }

        try {
            $reponse = $droneService->demarrerLivraison(
                ['lat' => $livraison->pickup_lat, 'lng' => $livraison->pickup_lng],
                ['lat' => $livraison->dropoff_lat, 'lng' => $livraison->dropoff_lng],
                $commande->id
            );
            $mission = $reponse['mission'] ?? [];

            $livraison->update([
                'mission_id' => $mission['mission_id'] ?? null,
                'drone_id' => $mission['drone_id'] ?? null,
                'statut' => $mission['status'] ?? LivraisonDrone::STATUT_ATTENTE_DRONE,
                'queue_position' => $mission['queue_position'] ?? null,
                'erreur' => null,
            ]);

            return back()->with('toast_success', 'Nouvelle tentative envoyée au service drone.');
        } catch (\Throwable $e) {
            $livraison->update([
                'statut' => LivraisonDrone::STATUT_ENVOI_IMPOSSIBLE,
                'erreur' => $e->getMessage(),
            ]);

            return back()->with('toast_error', "Le service drone reste injoignable : {$e->getMessage()}");
        }
    }

    private function autoriserAcces(Commande $commande): void
    {
        $user = Auth::user();

        $estClient = $commande->user_id === $user->id;
        $estVendeur = $commande->boutique->user_id === $user->id;
        $estAdmin = $user->isAdmin();

        abort_unless($estClient || $estVendeur || $estAdmin, 403);
    }

    /**
     * Persiste le nouveau statut s'il a changé et déclenche les
     * notifications sur les transitions clés (pas à chaque tick de
     * polling, seulement quand quelque chose de significatif se produit).
     */
    private function appliquerMiseAJour(Commande $commande, LivraisonDrone $livraison, array $mission): void
    {
        $nouveauStatut = $mission['status'] ?? $livraison->statut;
        $statutInchange = $nouveauStatut === $livraison->statut;

        $livraison->update([
            'statut' => $nouveauStatut,
            'drone_id' => $mission['drone_id'] ?? $livraison->drone_id,
            'queue_position' => $mission['queue_position'] ?? null,
            'erreur' => $mission['error'] ?? null,
        ]);

        if ($statutInchange) {
            return;
        }

        $this->notifierTransition($commande, $nouveauStatut);
    }

    private function notifierTransition(Commande $commande, string $statut): void
    {
        $vendeurId = $commande->boutique->user_id;
        $clientId = $commande->user_id;

        switch ($statut) {
            case LivraisonDrone::STATUT_DECOLLAGE:
                NotificationVendeur::create([
                    'user_id' => $vendeurId,
                    'commande_id' => $commande->id,
                    'type' => 'drone_decollage',
                    'message' => "Le drone a décollé pour récupérer la commande {$commande->reference}.",
                ]);
                break;

            case LivraisonDrone::STATUT_COLIS_RECUPERE:
                NotificationVendeur::create([
                    'user_id' => $vendeurId,
                    'commande_id' => $commande->id,
                    'type' => 'drone_colis_recupere',
                    'message' => "Le drone a récupéré le colis de la commande {$commande->reference}.",
                ]);
                NotificationClient::create([
                    'user_id' => $clientId,
                    'commande_id' => $commande->id,
                    'type' => 'drone_colis_recupere',
                    'message' => "Votre colis ({$commande->reference}) est en route par drone.",
                ]);
                break;

            case LivraisonDrone::STATUT_LIVRE:
                NotificationClient::create([
                    'user_id' => $clientId,
                    'commande_id' => $commande->id,
                    'type' => 'drone_livre',
                    'message' => "Le drone a atterri avec votre commande {$commande->reference}. Finalisation en cours.",
                ]);
                break;

            case LivraisonDrone::STATUT_TERMINE:
                // Le drone a terminé sa mission : c'est la preuve de livraison.
                // Plus besoin d'une confirmation manuelle du client — la
                // commande est marquée livrée/confirmée automatiquement ici.
                $commande->update([
                    'statut' => 'confirmee',
                    'client_confirme' => true,
                    'client_confirme_at' => now(),
                ]);

                NotificationClient::create([
                    'user_id' => $clientId,
                    'commande_id' => $commande->id,
                    'type' => 'drone_termine',
                    'message' => "Votre commande {$commande->reference} a été livrée par drone.",
                ]);
                NotificationVendeur::create([
                    'user_id' => $vendeurId,
                    'commande_id' => $commande->id,
                    'type' => 'drone_termine',
                    'message' => "La livraison par drone de la commande {$commande->reference} est terminée.",
                ]);
                break;

            case LivraisonDrone::STATUT_ERREUR:
                NotificationVendeur::create([
                    'user_id' => $vendeurId,
                    'commande_id' => $commande->id,
                    'type' => 'drone_erreur',
                    'message' => "Échec de la livraison par drone pour la commande {$commande->reference}. Une action est requise.",
                ]);
                break;
        }
    }
}
