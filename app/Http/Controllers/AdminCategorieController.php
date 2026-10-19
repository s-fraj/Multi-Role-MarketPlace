<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCategorieController extends Controller
{
    public function index(Request $request)
    {
        $categories = Categorie::withCount('produits')
            ->when($request->filled('recherche'), function ($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->query('recherche') . '%');
            })
            ->orderBy('nom')
            ->get();

        return view('admin.categories', [
            'categories' => $categories,
            'rechercheActive' => $request->query('recherche'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100', 'unique:categories,nom'],
        ]);

        Categorie::create([
            'nom' => $data['nom'],
            'slug' => Str::slug($data['nom']),
            'active' => true,
        ]);

        return back()->with('toast_success', 'Catégorie ajoutée.');
    }

    public function update(Request $request, Categorie $categorie)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100', 'unique:categories,nom,' . $categorie->id],
        ]);

        $categorie->update([
            'nom' => $data['nom'],
            'slug' => Str::slug($data['nom']),
        ]);

        return back()->with('toast_success', 'Catégorie renommée.');
    }

    public function toggle(Categorie $categorie)
    {
        $categorie->update(['active' => ! $categorie->active]);

        return response()->json([
            'success' => true,
            'active' => $categorie->active,
            'message' => $categorie->active ? 'Catégorie activée.' : 'Catégorie désactivée.',
        ]);
    }
}