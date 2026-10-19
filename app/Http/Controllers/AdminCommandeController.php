<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\NotificationClient;
use App\Models\NotificationVendeur;
use Illuminate\Http\Request;

class AdminCommandeController extends Controller
{
    public function index(Request $request)
    {
        $statut = $request->query('statut', 'en_attente');

        $commandes = Commande::with(['user', 'boutique', 'items.produit'])
            ->when(in_array($statut, ['en_attente', 'en_cours', 'confirmee', 'annulee']), fn ($q) => $q->where('statut', $statut))
            ->latest()
            ->paginate(20);

        return view('admin.commandes', [
            'commandes' => $commandes,
            'statutActif' => $statut,
            'compteurs' => [
                'en_attente' => Commande::where('statut', 'en_attente')->count(),
                'en_cours' => Commande::where('statut', 'en_cours')->count(),
                'confirmee' => Commande::where('statut', 'confirmee')->count(),
                'annulee' => Commande::where('statut', 'annulee')->count(),
            ],
        ]);
    }

    public function confirmer(Commande $commande)
    {
        $commande->update([
            'statut' => 'confirmee',
            'client_confirme' => true,
            'client_confirme_at' => now(),
        ]);

        NotificationVendeur::create([
            'user_id' => $commande->boutique->user_id,
            'commande_id' => $commande->id,
            'type' => 'commande_confirmee_admin',
            'message' => "La commande {$commande->reference} a été confirmée par l'administration.",
        ]);
        NotificationClient::create([
            'user_id' => $commande->user_id,
            'commande_id' => $commande->id,
            'type' => 'commande_confirmee_admin',
            'message' => "Votre commande {$commande->reference} a été confirmée par l'administration.",
        ]);

        return back()->with('toast_success', 'Commande confirmée.');
    }

    public function annuler(Request $request, Commande $commande)
    {
      $validated = $request->validate([
          'raison' => ['nullable', 'string', 'max:500'],
      ]);

      $raison = $validated['raison'] ?? null;

      $commande->update([
          'statut' => 'annulee',
          'raison_annulation' => $raison,
      ]);

      $msgV = "La commande {$commande->reference} a été annulée";
      $msgC = "Votre commande {$commande->reference} a été annulée";

      if (! $commande->vendeur_confirme) {
          $msgV .= " car vous n'avez pas confirmé la livraison dans les délais.";
          $msgC .= " car le vendeur n'a pas confirmé la livraison.";
      } elseif (! $commande->client_confirme) {
          $msgV .= " car le client n'a pas confirmé la réception.";
          $msgC .= " car vous n'avez pas confirmé la réception dans les délais.";
      } else {
          $msgV .= $raison ? " : {$raison}" : ".";
          $msgC .= $raison ? " : {$raison}" : ".";
      }

      NotificationVendeur::create([
          'user_id' => $commande->boutique->user_id,
          'commande_id' => $commande->id,
          'type' => 'commande_annulee',
          'message' => $msgV,
      ]);
      NotificationClient::create([
          'user_id' => $commande->user_id,
          'commande_id' => $commande->id,
          'type' => 'commande_annulee',
          'message' => $msgC,
      ]);

      return back()->with('toast_success', 'Commande annulée. Les deux parties ont été notifiées.');
    }
}