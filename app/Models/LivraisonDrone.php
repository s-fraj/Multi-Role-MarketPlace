<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LivraisonDrone extends Model
{
    protected $table = 'livraisons_drone';

    // Mirrors the Python service's own status strings exactly — the two
    // systems must never drift, so these are used as-is, not re-encoded.
    public const STATUT_ATTENTE_DRONE = 'Waiting for drone';
    public const STATUT_DECOLLAGE = 'Taking off';
    public const STATUT_VERS_RAMASSAGE = 'Going to pickup';
    public const STATUT_COLIS_RECUPERE = 'Package picked';
    public const STATUT_VERS_CLIENT = 'Going to client';
    public const STATUT_LIVRE = 'Delivered';
    public const STATUT_TERMINE = 'Completed';
    public const STATUT_ERREUR = 'Error';

    // Laravel-only: the initial call to the drone service failed outright
    // (service unreachable, bad response...) before any mission_id existed.
    public const STATUT_ENVOI_IMPOSSIBLE = 'Envoi impossible';

    // Order used to render the client-facing progress timeline.
    public const ETAPES = [
        self::STATUT_ATTENTE_DRONE,
        self::STATUT_DECOLLAGE,
        self::STATUT_VERS_RAMASSAGE,
        self::STATUT_COLIS_RECUPERE,
        self::STATUT_VERS_CLIENT,
        self::STATUT_LIVRE,
        self::STATUT_TERMINE,
    ];

    protected $fillable = [
        'commande_id', 'mission_id', 'drone_id', 'statut', 'queue_position',
        'erreur', 'pickup_lat', 'pickup_lng', 'dropoff_lat', 'dropoff_lng',
    ];

    protected $casts = [
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
        'dropoff_lat' => 'float',
        'dropoff_lng' => 'float',
    ];

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function libelle(): string
    {
        return match ($this->statut) {
            self::STATUT_ATTENTE_DRONE => $this->queue_position
                ? "En attente d'un drone disponible ({$this->queue_position}ᵉ dans la file)"
                : "En attente d'un drone disponible",
            self::STATUT_DECOLLAGE => 'Le drone décolle',
            self::STATUT_VERS_RAMASSAGE => 'Le drone se dirige vers le vendeur',
            self::STATUT_COLIS_RECUPERE => 'Colis récupéré par le drone',
            self::STATUT_VERS_CLIENT => 'Le drone se dirige vers vous',
            self::STATUT_LIVRE => 'Colis déposé — atterrissage en cours',
            self::STATUT_TERMINE => 'Livraison terminée',
            self::STATUT_ERREUR => 'Échec de la livraison par drone',
            self::STATUT_ENVOI_IMPOSSIBLE => 'Le service de livraison par drone est indisponible',
            default => 'Statut inconnu',
        };
    }

    public function etapeIndex(): ?int
    {
        $index = array_search($this->statut, self::ETAPES, true);

        return $index === false ? null : $index;
    }

    public function estEnErreur(): bool
    {
        return in_array($this->statut, [self::STATUT_ERREUR, self::STATUT_ENVOI_IMPOSSIBLE], true);
    }

    public function estTerminee(): bool
    {
        return $this->statut === self::STATUT_TERMINE;
    }
}
