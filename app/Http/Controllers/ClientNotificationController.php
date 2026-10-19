<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class ClientNotificationController extends Controller
{
    /**
     * /notifications — page notifications, réservée aux comptes client.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isVendeur()) {
            return redirect()
                ->route('produits.index')
                ->with('toast_error', "Vous n'avez pas cette option.");
        }

        $notifications = $user->notificationsClient()->latest()->get();

        $user->notificationsClient()->where('lu', false)->update(['lu' => true]);

        return view('profile.client.notifications', [
            'notifications' => $notifications,
        ]);
    }
}
