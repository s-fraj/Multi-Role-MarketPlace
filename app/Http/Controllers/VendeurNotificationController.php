<?php

namespace App\Http\Controllers;

use App\Models\NotificationVendeur;
use Illuminate\Support\Facades\Auth;

class VendeurNotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notificationsVendeur()->with('produit')->latest()->get();

        Auth::user()->notificationsVendeur()->where('lu', false)->update(['lu' => true]);

        return view('profile.vendeur.notifications', [
                    'notifications' => $notifications,
        ]);
    }
}
