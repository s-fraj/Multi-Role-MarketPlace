<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'nom', 'categorie', 'description', 'telephone', 'statut', 'banque', 'titulaire_compte', 'rib', 'rib_verifie'])]
class Boutique extends Model
{
    protected function casts(): array
    {
        return [
            'rib_verifie' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function produits()
    {
        return $this->hasMany(Produit::class);
    }
}
