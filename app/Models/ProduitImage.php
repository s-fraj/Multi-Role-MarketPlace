<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['produit_id', 'path', 'ordre'])]
class ProduitImage extends Model
{
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function url(): string
    {
        return asset('storage/'.$this->path);
    }
}
