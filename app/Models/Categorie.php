<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    protected $fillable = ['nom', 'slug', 'active'];

    public function produits()
    {
        return $this->hasMany(Produit::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}