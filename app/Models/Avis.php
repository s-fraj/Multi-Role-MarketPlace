<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Avis extends Model
{
    protected $table = 'avis';

    protected $fillable = ['user_id', 'produit_id', 'commande_id', 'note', 'commentaire'];

    protected function casts(): array
    {
        return [
            'note' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(AvisPhoto::class)->orderBy('ordre');
    }
}
