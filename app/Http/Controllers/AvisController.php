<?php

namespace App\Http\Controllers;

use App\Models\Avis;
use App\Models\CommandeItem;
use App\Models\NotificationVendeur;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AvisController extends Controller
{
    /**
     * GET /compte/avis — liste des avis publiés par le client connecté.
     */
    public function mesAvis()
    {
        $avis = Avis::with(['user', 'produit.images', 'produit.boutique', 'photos'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('profile.client.avis', [
            'mesAvis' => $avis,
        ]);
    }

    /**
     * POST /produits/{produit}/avis — nouvel avis (note + commentaire +
     * au moins une photo), réservé aux clients ayant reçu ce produit.
     */
    public function store(Request $request, Produit $produit)
    {
        $user = Auth::user();

        if (! $produit->peutEtreNoteParClient($user)) {
            abort(403, "Seuls les clients ayant reçu ce produit peuvent laisser un avis.");
        }

        if ($produit->avisDe($user)) {
            return back()->with('toast_error', 'Tu as déjà laissé un avis pour ce produit — modifie-le plutôt.');
        }

        $data = $request->validate([
            'note' => ['required', 'integer', 'min:1', 'max:5'],
            'commentaire' => ['required', 'string', 'max:2000'],
            'photos' => ['required', 'array', 'min:1'],
            'photos.*' => ['image', 'max:5120'],
        ]);

        $commandeId = CommandeItem::where('produit_id', $produit->id)
            ->whereHas('commande', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('statut', 'confirmee');
            })
            ->latest('id')
            ->value('commande_id');

        $avis = Avis::create([
            'user_id' => $user->id,
            'produit_id' => $produit->id,
            'commande_id' => $commandeId,
            'note' => $data['note'],
            'commentaire' => $data['commentaire'],
        ]);

        foreach ($request->file('photos', []) as $i => $photo) {
            $path = $photo->store('avis', 'public');
            $avis->photos()->create(['path' => $path, 'ordre' => $i]);
        }

        $etoiles = str_repeat('★', $data['note']).str_repeat('☆', 5 - $data['note']);
        NotificationVendeur::create([
            'user_id' => $produit->boutique->user_id,
            'produit_id' => $produit->id,
            'type' => NotificationVendeur::TYPE_NOUVEL_AVIS,
            'message' => "{$user->name} a laissé un avis {$etoiles} sur « {$produit->nom} ».",
        ]);

        return back()->with('toast_success', 'Merci pour ton avis !')->withFragment('avis');
    }

    /**
     * PUT /avis/{avis} — modification par son auteur : note, commentaire,
     * suppression de photos existantes, ajout de nouvelles (au moins une
     * photo doit rester au total).
     */
    public function update(Request $request, Avis $avis)
    {
        if ($avis->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'note' => ['required', 'integer', 'min:1', 'max:5'],
            'commentaire' => ['required', 'string', 'max:2000'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'max:5120'],
            'photos_supprimees' => ['nullable', 'array'],
            'photos_supprimees.*' => ['integer'],
        ]);

        $photosASupprimer = $data['photos_supprimees'] ?? [];
        $nouvellesPhotos = $request->file('photos', []);
        $totalApres = $avis->photos()->count() - count($photosASupprimer) + count($nouvellesPhotos);

        if ($totalApres < 1) {
            return back()->with('toast_error', 'Il doit rester au moins une photo sur ton avis.');
        }

        $avis->update([
            'note' => $data['note'],
            'commentaire' => $data['commentaire'],
        ]);

        foreach ($photosASupprimer as $photoId) {
            $photo = $avis->photos()->find($photoId);
            if ($photo) {
                Storage::disk('public')->delete($photo->path);
                $photo->delete();
            }
        }

        $ordre = $avis->photos()->count();
        foreach ($nouvellesPhotos as $photo) {
            $path = $photo->store('avis', 'public');
            $avis->photos()->create(['path' => $path, 'ordre' => $ordre++]);
        }

        $etoiles = str_repeat('★', $data['note']).str_repeat('☆', 5 - $data['note']);
        NotificationVendeur::create([
            'user_id' => $avis->produit->boutique->user_id,
            'produit_id' => $avis->produit_id,
            'type' => NotificationVendeur::TYPE_AVIS_MODIFIE,
            'message' => "{$avis->user->name} a modifié son avis ({$etoiles}) sur « {$avis->produit->nom} ».",
        ]);

        return back()->with('toast_success', 'Avis modifié.')->withFragment('avis');
    }

    /**
     * DELETE /avis/{avis} — suppression par son auteur.
     */
    public function destroy(Avis $avis)
    {
        if ($avis->user_id !== Auth::id()) {
            abort(403);
        }

        foreach ($avis->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }

        $avis->delete();

        return back()->with('toast_success', 'Avis supprimé.')->withFragment('avis');
    }
}
