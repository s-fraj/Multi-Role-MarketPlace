<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClientParametresController extends Controller
{
    public function index()
    {
        return view('profile.client.parametres');
    }

    /**
     * PATCH /parametres/profil
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
     * PATCH /parametres/adresse
     */
    public function updateAdresse(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
        ]);

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Adresse mise à jour.',
        ]);
    }

    /**
     * PATCH /parametres/mot-de-passe
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
     * PATCH /parametres/notifications
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
     * DELETE /parametres/compte
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
