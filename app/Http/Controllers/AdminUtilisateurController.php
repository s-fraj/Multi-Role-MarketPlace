<?php

namespace App\Http\Controllers;

use App\Models\NotificationClient;
use App\Models\NotificationVendeur;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUtilisateurController extends Controller
{
    public function index(Request $request)
    {
        $utilisateurs = User::with('boutique')
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->query('role')))
            ->when($request->filled('recherche'), function ($q) use ($request) {
                $terme = $request->query('recherche');
                $q->where(function ($sub) use ($terme) {
                    $sub->where('name', 'like', "%{$terme}%")->orWhere('email', 'like', "%{$terme}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.utilisateurs', [
            'utilisateurs' => $utilisateurs,
            'roleActif' => $request->query('role'),
            'rechercheActive' => $request->query('recherche'),
        ]);
    }

    /**
     * PATCH /admin/utilisateurs/{utilisateur}/blocage
     */
    public function toggleBlocage(User $utilisateur)
    {
        if ($utilisateur->isAdmin()) {
            return response()->json(['success' => false, 'message' => "Impossible de bloquer un compte administrateur."], 422);
        }

        $utilisateur->update(['est_bloque' => ! $utilisateur->est_bloque]);

        $message = $utilisateur->est_bloque
            ? "Ton compte a été bloqué par un administrateur."
            : "Ton compte a été débloqué, tu peux de nouveau utiliser Souq normalement.";

        if ($utilisateur->isVendeur()) {
            NotificationVendeur::create([
                'user_id' => $utilisateur->id,
                'type' => $utilisateur->est_bloque ? NotificationVendeur::TYPE_COMPTE_BLOQUE : NotificationVendeur::TYPE_COMPTE_DEBLOQUE,
                'message' => $message,
            ]);
        } else {
            NotificationClient::create([
                'user_id' => $utilisateur->id,
                'type' => $utilisateur->est_bloque ? NotificationClient::TYPE_COMPTE_BLOQUE : NotificationClient::TYPE_COMPTE_DEBLOQUE,
                'message' => $message,
            ]);
        }

        return response()->json([
            'success' => true,
            'bloque' => $utilisateur->est_bloque,
            'message' => $utilisateur->est_bloque
                ? "Le compte de {$utilisateur->name} a été bloqué."
                : "Le compte de {$utilisateur->name} a été débloqué.",
        ]);
    }
}
