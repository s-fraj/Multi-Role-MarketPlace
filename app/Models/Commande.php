<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Commande extends Model
{
    protected $fillable = [
        'user_id', 'boutique_id', 'reference', 'total', 'frais_livraison',
        'statut', 'nom_client', 'telephone', 'adresse', 'ville', 'code_postal',
        'latitude', 'longitude', 'adresse_vendeur', 'latitude_vendeur', 'longitude_vendeur',
        'vendeur_confirme', 'client_confirme', 'vendeur_confirme_at',
        'client_confirme_at', 'raison_annulation',
    ];

    protected $casts = [
        'vendeur_confirme' => 'boolean',
        'client_confirme' => 'boolean',
        'vendeur_confirme_at' => 'datetime',
        'client_confirme_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function boutique(): BelongsTo
    {
        return $this->belongsTo(Boutique::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CommandeItem::class);
    }

    public function livraisonDrone(): HasOne
    {
        return $this->hasOne(LivraisonDrone::class);
    }

    public function lienCarte(): ?string
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        return "https://www.openstreetmap.org/?mlat={$this->latitude}&mlon={$this->longitude}#map=17/{$this->latitude}/{$this->longitude}";
    }

    public function lienCarteVendeur(): ?string
    {
        if ($this->latitude_vendeur === null || $this->longitude_vendeur === null) {
            return null;
        }

        return "https://www.openstreetmap.org/?mlat={$this->latitude_vendeur}&mlon={$this->longitude_vendeur}#map=17/{$this->latitude_vendeur}/{$this->longitude_vendeur}";
    }

    public function scopeEnAttente($query) { return $query->where('statut', 'en_attente'); }
    public function scopeEnCours($query) { return $query->where('statut', 'en_cours'); }
    public function scopeConfirmee($query) { return $query->where('statut', 'confirmee'); }
    public function scopeAnnulee($query) { return $query->where('statut', 'annulee'); }
}