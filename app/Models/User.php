<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'address', 'city', 'postal_code', 'notifications_preferences', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notifications_preferences' => 'array',
            'est_bloque' => 'boolean',
        ];
    }

    const NOTIFICATIONS_PAR_DEFAUT = [
        'nouvelle_commande' => true,
        'avis_client' => true,
        'litige_ouvert' => true,
        'stock_faible' => true,
        'newsletter' => false,
    ];

    /**
     * Préférences de notifications avec valeurs par défaut pour les clés absentes.
     */
    public function notificationsPreferences(): array
    {
        return array_merge(self::NOTIFICATIONS_PAR_DEFAUT, $this->notifications_preferences ?? []);
    }

    public function isVendeur(): bool
    {
        return $this->role === 'vendeur';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Route name to land on after login/register, and for the header "Souk" link.
     * Clients and vendeurs share the same dashboard; admins get their own.
     */
    public function homeRouteName(): string
    {
        if ($this->isAdmin()) {
            return 'admin.dashboard';
        }

        return 'dashboard';
    }

    /**
     * Route name for the account/profile page, role-aware.
     */
    public function profileRouteName(): string
    {
        if ($this->isAdmin()) {
            return 'admin.dashboard';
        }

        return $this->isVendeur() ? 'vendeur.tableau-de-bord' : 'compte';
    }

    public function boutique()
    {
        return $this->hasOne(Boutique::class);
    }

    /**
     * Public URL of the user's profile photo, or null if they haven't set one
     * (views fall back to showing initials in that case).
     */
    public function avatarUrl(): ?string
    {
        return $this->avatar ? asset('storage/'.$this->avatar) : null;
    }

    /**
     * Up-to-two-letter initials from the user's name, used as the avatar
     * placeholder everywhere a real photo hasn't been uploaded.
     */
    public function initials(): string
    {
        $parts = collect(preg_split('/\s+/', trim($this->name)))->filter();

        return $parts->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
    }

    public function panier()
    {
        return $this->hasOne(Panier::class);
    }

    public function favoris()
    {
        return $this->hasMany(Favori::class);
    }

    /**
     * Fil d'activité vendeur (décisions de modération, etc.) — distinct de la
     * relation `notifications()` de Notifiable, qui reste pour les emails Breeze.
     */
    public function notificationsVendeur()
    {
        return $this->hasMany(NotificationVendeur::class);
    }

    /**
     * Fil de notifications côté client (décisions admin sur le compte, etc.).
     */
    public function notificationsClient()
    {
        return $this->hasMany(NotificationClient::class);
    }
}
