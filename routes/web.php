<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminProduitController;
use App\Http\Controllers\AdminUtilisateurController;
use App\Http\Controllers\ClientNotificationController;
use App\Http\Controllers\ClientParametresController;
use App\Http\Controllers\FavoriController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PanierController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilePhotoController;
use App\Http\Controllers\VendeurNotificationController;
use App\Http\Controllers\VendeurParametresController;
use App\Models\Produit;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminCommandeController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\AdminCategorieController;
use App\Http\Controllers\DroneLivraisonController;
use App\Http\Controllers\AvisController;


// "dashboard" is the shared landing page both clients and vendeurs land on
// after login/register (see User::homeRouteName()) and that the "Souk" logo
// links back to. It's also the name Breeze's auth flow (email verification,
// password confirmation...) redirects to internally, so the name stays as-is.
Route::get('/dashboard', function () {
    return view('profile.dashboard', [
        'nouveautes' => Produit::enLigne()->with(['boutique', 'images'])->latest()->take(4)->get(),
        'categories' => \App\Models\Categorie::active()
            ->withCount(['produits' => fn ($q) => $q->enLigne()])
            ->orderByDesc('produits_count')
            ->take(6)
            ->get(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Account overview page (avatar, stats, recent orders) — opened from the header "Profil" dropdown.
    Route::get('/compte', [ProfileController::class, 'dashboard'])->name('compte');
    Route::post('/profile/photo', [ProfilePhotoController::class, 'update'])->name('profile.photo.update');
    Route::delete('/profile/photo', [ProfilePhotoController::class, 'destroy'])->name('profile.photo.destroy');

    Route::get('/parametres', [ClientParametresController::class, 'index'])->name('profile.edit');
    Route::patch('/parametres/profil', [ClientParametresController::class, 'updateProfil'])->name('parametres.profil');
    Route::patch('/parametres/adresse', [ClientParametresController::class, 'updateAdresse'])->name('parametres.adresse');
    Route::patch('/parametres/mot-de-passe', [ClientParametresController::class, 'updateMotDePasse'])->name('parametres.mot-de-passe');
    Route::patch('/parametres/notifications', [ClientParametresController::class, 'updateNotifications'])->name('parametres.notifications');
    Route::delete('/parametres/compte', [ClientParametresController::class, 'destroyCompte'])->name('parametres.compte.supprimer');

    // Notifications côté client — page dédiée + endpoints partagés utilisés
    // par la clochette du header (client) et le badge du menu vendeur.
    Route::get('/notifications', [ClientNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/recentes', [NotificationController::class, 'recentes'])->name('notifications.recentes');
    Route::post('/notifications/marquer-lues', [NotificationController::class, 'marquerLues'])->name('notifications.marquer-lues');
   
    Route::get('/compte/commandes', [CommandeController::class, 'clientIndex'])->name('client.commandes');
    Route::post('/commandes', [CommandeController::class, 'store'])->name('commandes.store');

    Route::get('/commandes/{commande}/suivi', [CommandeController::class, 'suivi'])->name('commandes.suivi');
    Route::get('/commandes/{commande}/statut-drone', [DroneLivraisonController::class, 'statut'])->name('commandes.statut-drone');

    Route::post('/produits/{produit}/avis', [AvisController::class, 'store'])->name('avis.store');
    Route::get('/compte/avis', [AvisController::class, 'mesAvis'])->name('client.avis');
    Route::put('/avis/{avis}', [AvisController::class, 'update'])->name('avis.update');
    Route::delete('/avis/{avis}', [AvisController::class, 'destroy'])->name('avis.destroy');
       
    
    
    
    // Vendeur-only pages.
    Route::middleware('vendeur')->prefix('vendeur')->name('vendeur.')->group(function () {
        // NEW: vendor real dashboard
        Route::get('/tableau-de-bord', [VendeurParametresController::class, 'dashboard'])->name('tableau-de-bord');

        Route::get('/boutique', function () {
            return view('profile.vendeur.vendeur-boutique', ['produit' => null]);
        })->name('boutique');

        Route::post('/produits', [ProduitController::class, 'store'])->name('produits.store');
        Route::get('/produits', [ProduitController::class, 'mesProduits'])->name('produits.index');
        Route::get('/produits/{produit}/modifier', [ProduitController::class, 'edit'])->name('produits.edit');
        Route::patch('/produits/{produit}', [ProduitController::class, 'update'])->name('produits.update');
        Route::delete('/produits/{produit}', [ProduitController::class, 'destroy'])->name('produits.destroy');

        // FIXED: commandes now has a real route
        Route::get('/commandes', [CommandeController::class, 'vendeurIndex'])->name('commandes');
        Route::patch('/commandes/{commande}/livraison', [CommandeController::class, 'confirmerLivraison'])->name('commandes.livraison');

        // RENAMED: statistiques → notifications
        Route::get('/notifications', [VendeurNotificationController::class, 'index'])->name('notifications');

        Route::get('/parametres', [VendeurParametresController::class, 'index'])->name('parametres');
        Route::patch('/parametres/profil', [VendeurParametresController::class, 'updateProfil'])->name('parametres.profil');
        Route::patch('/parametres/mot-de-passe', [VendeurParametresController::class, 'updateMotDePasse'])->name('parametres.mot-de-passe');
        Route::patch('/parametres/banque', [VendeurParametresController::class, 'updateBanque'])->name('parametres.banque');
        Route::patch('/parametres/notifications', [VendeurParametresController::class, 'updateNotifications'])->name('parametres.notifications');
        Route::patch('/parametres/boutique-statut', [VendeurParametresController::class, 'toggleBoutiqueStatut'])->name('parametres.boutique-statut');
        Route::delete('/parametres/compte', [VendeurParametresController::class, 'destroyCompte'])->name('parametres.compte.supprimer');
    });
    // Admin-only pages.
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('/utilisateurs', [AdminUtilisateurController::class, 'index'])->name('utilisateurs');
        Route::patch('/utilisateurs/{utilisateur}/blocage', [AdminUtilisateurController::class, 'toggleBlocage'])->name('utilisateurs.blocage');

        Route::patch('/commandes/{commande}/confirmer', [AdminCommandeController::class, 'confirmer'])->name('commandes.confirmer');
        Route::patch('/commandes/{commande}/annuler', [AdminCommandeController::class, 'annuler'])->name('commandes.annuler');

        Route::get('/commandes/drones', [DroneLivraisonController::class, 'admin'])->name('commandes.drones');
        Route::post('/commandes/{commande}/drone/reessayer', [DroneLivraisonController::class, 'reessayer'])->name('commandes.drone.reessayer');
        Route::get('/produits', [AdminProduitController::class, 'index'])->name('produits');
        Route::get('/produits-en-attente', [AdminProduitController::class, 'enAttente'])->name('produits.en-attente');
        Route::patch('/produits/{produit}/approuver', [AdminProduitController::class, 'approuver'])->name('produits.approuver');
        Route::patch('/produits/{produit}/refuser', [AdminProduitController::class, 'refuser'])->name('produits.refuser');


        Route::get('/categories', [AdminCategorieController::class, 'index'])->name('categories');
        Route::post('/categories', [AdminCategorieController::class, 'store'])->name('categories.store');
        Route::patch('/categories/{categorie}', [AdminCategorieController::class, 'update'])->name('categories.update');
        Route::patch('/categories/{categorie}/toggle', [AdminCategorieController::class, 'toggle'])->name('categories.toggle');
        Route::get('/commandes', [AdminCommandeController::class, 'index'])->name('commandes');
    });
});

Route::get('/', function () {
    return view('home', [
        'nouveautes' => Produit::enLigne()->with(['boutique', 'images'])->latest()->take(4)->get(),
        'categories' => \App\Models\Categorie::active()
            ->withCount(['produits' => fn ($q) => $q->enLigne()])
            ->orderByDesc('produits_count')
            ->take(6)
            ->get(),
    ]);
})->name('home');



Route::get('/produits', [ProduitController::class, 'index'])->name('produits.index');

Route::get('/produits/suggestions', [ProduitController::class, 'suggestions'])->name('produits.suggestions');


Route::get('/produit-detail/{produit}', [ProduitController::class, 'show'])->name('produit-detail.index');

Route::get('/panier', [PanierController::class, 'index'])->middleware('auth')->name('panier.index');

Route::get('/favoris', [FavoriController::class, 'index'])->middleware('auth')->name('favoris.index');

Route::middleware('auth')->group(function () {
    Route::post('/panier/ajouter/{produit}', [PanierController::class, 'store'])->name('panier.ajouter');
    Route::patch('/panier/{panierItem}', [PanierController::class, 'update'])->name('panier.update');
    Route::delete('/panier/{panierItem}', [PanierController::class, 'destroy'])->name('panier.supprimer');

    Route::post('/favoris/toggle/{produit}', [FavoriController::class, 'toggle'])->name('favoris.toggle');
});



require __DIR__.'/auth.php';
