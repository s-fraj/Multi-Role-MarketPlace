<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'boutique_id', 'nom', 'slug', 'description', 'categorie_id', 'sku',
    'prix', 'prix_barre', 'stock', 'poids', 'variantes', 'statut', 'raison_refus',
])]
class Produit extends Model
{
    const STATUT_BROUILLON = 'brouillon';
    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_EN_LIGNE = 'en_ligne';
    const STATUT_REFUSE = 'refuse';

    protected function casts(): array
    {
        return [
            'prix' => 'decimal:2',
            'prix_barre' => 'decimal:2',
            'poids' => 'decimal:2',
            'variantes' => 'array',
        ];
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }

    public function images()
    {
        return $this->hasMany(ProduitImage::class)->orderBy('ordre');
    }

    public function cover()
    {
        return $this->images->first();
    }

    public function avis()
    {
        return $this->hasMany(Avis::class)->latest();
    }

    /**
     * Moyenne des notes, arrondie à l'entier le plus proche pour l'affichage
     * des étoiles (1 décimale conservée pour le texte "4.3/5" si besoin).
     */
    public function noteMoyenne(): float
    {
        return round($this->avis()->avg('note') ?? 0, 1);
    }

    public function avisCount(): int
    {
        return $this->avis()->count();
    }

    /**
     * Un client ne peut noter un produit que s'il a une commande livrée
     * (drone terminé -> statut "confirmee") contenant ce produit.
     */
    public function peutEtreNoteParClient(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return CommandeItem::where('produit_id', $this->id)
            ->whereHas('commande', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('statut', 'confirmee');
            })
            ->exists();
    }

    public function avisDe(?User $user): ?Avis
    {
        if (! $user) {
            return null;
        }

        return $this->avis()->where('user_id', $user->id)->first();
    }

    /**
     * Produits visibles dans le catalogue public : validés par un administrateur.
     */
    public function scopeEnLigne($query)
    {
        return $query->where('statut', self::STATUT_EN_LIGNE);
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', self::STATUT_EN_ATTENTE);
    }

    /**
     * Libellé + couleur du badge de statut, pour l'affichage vendeur/admin.
     */
    public function statutBadge(): array
    {
        return match ($this->statut) {
            self::STATUT_EN_LIGNE => ['texte' => 'En ligne', 'classe' => 'statut-en-ligne'],
            self::STATUT_EN_ATTENTE => ['texte' => 'En attente de validation', 'classe' => 'statut-en-attente'],
            self::STATUT_REFUSE => ['texte' => 'Refusé', 'classe' => 'statut-refuse'],
            default => ['texte' => 'Brouillon', 'classe' => 'statut-brouillon'],
        };
    }

    /**
     * Attributs suggérés par catégorie — pré-remplis dans le formulaire vendeur
     * mais restent modifiables/supprimables, pas une contrainte en base.
     */
    public static function attributsSuggeres(): array
    {
        return [
            'Mode & Textile' => ['Taille', 'Couleur'],
            'Enfants' => ['Taille', 'Âge recommandé', 'Couleur'],
            'Maison & Artisanat' => ['Couleur', 'Dimensions (L x l x H)'],
            'Électronique' => ['Couleur', 'Modèle / Capacité'],
            'Beauté' => ['Contenance'],
            'Épicerie fine' => ['Poids / Contenance'],
        ];
    }

    /**
     * Generate a unique slug from the product name, used in the produit-detail URL.
     */
    public static function slugFor(string $nom): string
    {
        $base = Str::slug($nom);
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
