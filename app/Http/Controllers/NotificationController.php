<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * GET /notifications/recentes — interrogé toutes les ~15s par la clochette
     * du header (client + vendeur) pour donner un effet temps réel sans avoir
     * besoin d'un serveur websocket. Renvoie le nombre de non-lues + les 5
     * dernières notifications, à partir de la table propre au rôle courant.
     */
    public function recentes()
    {
        $user = Auth::user();

        if (! $user || $user->isAdmin()) {
            return response()->json(['nonLues' => 0, 'notifications' => []]);
        }

        $query = $user->isVendeur() ? $user->notificationsVendeur() : $user->notificationsClient();

        return response()->json([
            'nonLues' => (clone $query)->where('lu', false)->count(),
            'notifications' => (clone $query)->latest()->take(5)->get()->map(fn ($notification) => [
                'id' => $notification->id,
                'message' => $notification->message,
                'icone' => $notification->icone(),
                'type' => $notification->type,
                'lu' => $notification->lu,
                'date' => $notification->created_at->diffForHumans(),
                'lien' => method_exists($notification, 'lien') ? $notification->lien() : null,
            ]),
        ]);
    }

    /**
     * POST /notifications/marquer-lues — appelé à l'ouverture de la clochette,
     * marque tout comme lu pour le rôle courant (sans recharger la page).
     */
    public function marquerLues()
    {
        $user = Auth::user();

        if (! $user || $user->isAdmin()) {
            return response()->json(['success' => true]);
        }

        $query = $user->isVendeur() ? $user->notificationsVendeur() : $user->notificationsClient();
        $query->where('lu', false)->update(['lu' => true]);

        return response()->json(['success' => true]);
    }
}
