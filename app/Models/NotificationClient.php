<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class NotificationClient extends Model
{
    const TYPE_COMPTE_BLOQUE = 'compte_bloque';
    const TYPE_COMPTE_DEBLOQUE = 'compte_debloque';

    // Réservé pour de futurs événements (baisse de prix / retour en stock
    // d'un produit favori...), pas encore déclenché nulle part.
    const TYPE_FAVORI_INFO = 'favori_info';
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
            self::TYPE_COMPTE_BLOQUE => 'ti-lock',
            self::TYPE_COMPTE_DEBLOQUE => 'ti-lock-open',
            self::TYPE_FAVORI_INFO => 'ti-heart',
            'drone_colis_recupere', 'drone_livre', 'drone_termine' => 'ti-drone',
            default => 'ti-bell',
        };
    }
}
