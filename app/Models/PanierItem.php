<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['panier_id', 'produit_id', 'quantite'])]
class PanierItem extends Model
{
    public function panier()
    {
        return $this->belongsTo(Panier::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function sousTotal(): float
    {
        return (float) $this->produit->prix * $this->quantite;
    }
}
