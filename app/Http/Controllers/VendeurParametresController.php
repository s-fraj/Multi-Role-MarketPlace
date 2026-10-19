<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\Commande;

class VendeurParametresController extends Controller
{
    
    public function dashboard()
    {
        $user = Auth::user();
        $boutique = $user->boutique;

        $stats = [
            'en_cours' => Commande::where('boutique_id', $boutique?->id)->where('statut', 'en_cours')->count(),
            'livrees' => Commande::where('boutique_id', $boutique?->id)->where('statut', 'confirmee')->count(),
            'produits_actifs' => $boutique?->produits()->where('statut', 'en_ligne')->count() ?? 0,
            'notifications_non_lues' => $user->notificationsVendeur()->where('lu', false)->count(),
        ];

        $recentOrders = Commande::with(['user', 'items.produit'])
            ->where('boutique_id', $boutique?->id)
            ->latest()
            ->take(3)
            ->get();

        return view('profile.vendeur.tableau-de-bord', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
        ]);
    }


    public function index()
    {
        $user = Auth::user();
        $boutique = $user->boutique;

        return view('profile.vendeur.vendeur-param', [
            'boutique' => $boutique,
        ]);
    }

    /**
     * PATCH /vendeur/parametres/profil
     */
    public function updateProfil(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'prenom' => ['required', 'string', 'max:100'],
            'nom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update([
            'name' => trim($data['prenom'].' '.$data['nom']),
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Informations personnelles mises à jour.',
        ]);
    }

    /**
     * PATCH /vendeur/parametres/mot-de-passe
     */
    public function updateMotDePasse(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'mot_de_passe_actuel' => ['required', 'string'],
            'nouveau_mot_de_passe' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($data['mot_de_passe_actuel'], $user->password)) {
            throw ValidationException::withMessages([
                'mot_de_passe_actuel' => "Le mot de passe actuel est incorrect.",
            ]);
        }

        $user->update(['password' => Hash::make($data['nouveau_mot_de_passe'])]);
        return response()->json([
            'success' => true,
            'message' => 'Mot de passe mis à jour.',
        ]);
    }

    /**
     * PATCH /vendeur/parametres/banque
     */
    public function updateBanque(Request $request)
    {
        $boutique = Auth::user()->boutique;

        if (! $boutique) {
            return response()->json(['success' => false, 'message' => "Aucune boutique associée à ce compte."], 404);
        }

        $data = $request->validate([
            'banque' => ['required', 'string', 'max:100'],
            'titulaire_compte' => ['required', 'string', 'max:150'],
            'rib' => ['required', 'string', 'max:34'],
        ]);

        // Toute modification du RIB doit repasser par une vérification.
        $boutique->update($data + ['rib_verifie' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Coordonnées bancaires mises à jour — en attente de vérification.',
        ]);
    }

    /**
     * PATCH /vendeur/parametres/notifications
     */
    public function updateNotifications(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'cle' => ['required', 'string', Rule::in(array_keys(User::NOTIFICATIONS_PAR_DEFAUT))],
            'valeur' => ['required', 'boolean'],
        ]);

        $preferences = $user->notificationsPreferences();
        $preferences[$data['cle']] = $data['valeur'];

        $user->update(['notifications_preferences' => $preferences]);

        return response()->json(['success' => true]);
    }

    /**
     * PATCH /vendeur/parametres/boutique-statut
     */
    public function toggleBoutiqueStatut(Request $request)
    {
        $boutique = Auth::user()->boutique;

        if (! $boutique) {
            return response()->json(['success' => false, 'message' => "Aucune boutique associée à ce compte."], 404);
        }

        $boutique->update([
            'statut' => $boutique->statut === 'active' ? 'inactive' : 'active',
        ]);

        return response()->json([
            'success' => true,
            'statut' => $boutique->statut,
            'message' => $boutique->statut === 'active'
                ? 'Ta boutique est de nouveau visible dans le catalogue.'
                : 'Ta boutique est désactivée — elle et ses produits sont masqués du catalogue.',
        ]);
    }

    /**
     * DELETE /vendeur/parametres/compte
     */
    public function destroyCompte(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'mot_de_passe' => ['required', 'string'],
        ]);

        if (! Hash::check($data['mot_de_passe'], $user->password)) {
            throw ValidationException::withMessages([
                'mot_de_passe' => "Mot de passe incorrect.",
            ]);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'redirect' => route('home'),
        ]);
    }
}
