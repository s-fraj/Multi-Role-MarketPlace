<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class NotificationVendeur extends Model
{
    const TYPE_PRODUIT_APPROUVE = 'produit_approuve';
    const TYPE_PRODUIT_REFUSE = 'produit_refuse';
    const TYPE_COMPTE_BLOQUE = 'compte_bloque';
    const TYPE_COMPTE_DEBLOQUE = 'compte_debloque';
    const TYPE_NOUVEL_AVIS = 'nouvel_avis';
    const TYPE_AVIS_MODIFIE = 'avis_modifie';
    protected $fillable = ['user_id', 'produit_id', 'commande_id', 'type', 'message', 'lu'];

    protected function casts(): array
    {
        return [
            'lu' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function icone(): string
    {
        return match ($this->type) {
            self::TYPE_PRODUIT_APPROUVE => 'ti-circle-check',
            self::TYPE_PRODUIT_REFUSE => 'ti-circle-x',
            self::TYPE_COMPTE_BLOQUE => 'ti-lock',
            self::TYPE_COMPTE_DEBLOQUE => 'ti-lock-open',
            self::TYPE_NOUVEL_AVIS, self::TYPE_AVIS_MODIFIE => 'ti-star',
            'drone_decollage', 'drone_colis_recupere', 'drone_termine' => 'ti-drone',
            'drone_erreur' => 'ti-alert-triangle',
            default => 'ti-bell',
        };
    }

    /**
     * URL vers laquelle un clic sur cette notification doit mener, si
     * applicable. Un nouvel avis mène droit à la section avis du produit
     * concerné ; une décision de modération mène à la liste des produits.
     */
    public function lien(): ?string
    {
        if (! $this->produit_id) {
            return null;
        }

        return match ($this->type) {
            self::TYPE_NOUVEL_AVIS, self::TYPE_AVIS_MODIFIE => route('produit-detail.index', $this->produit_id).'#avis',
            self::TYPE_PRODUIT_APPROUVE, self::TYPE_PRODUIT_REFUSE => route('vendeur.produits.index'),
            default => null,
        };
    }
}
