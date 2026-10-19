<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.marketplace-layout', function ($view) {
            $panierCount = 0;
            $produitsEnLigneCount = 0;

            if (Auth::check() && ! Auth::user()->isVendeur()) {
                $panierCount = Auth::user()->panier?->nombreArticles() ?? 0;
            }

            if (Auth::check() && Auth::user()->isVendeur()) {
                $produitsEnLigneCount = Auth::user()->boutique?->produits()->enLigne()->count() ?? 0;
            }

            $view->with('panierCount', $panierCount);
            $view->with('produitsEnLigneCount', $produitsEnLigneCount);
            $view->with('categoriesMenu', \App\Models\Categorie::active()->orderBy('nom')->pluck('nom'));
        });

        // Branded "mot de passe oublié" email — sent immediately (synchronously)
        // when Password::sendResetLink() runs, using whatever MAIL_MAILER is
        // configured in .env.
        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

            return (new MailMessage)
                ->subject('Réinitialise ton mot de passe — Souk')
                ->view('emails.reset-password', [
                    'url' => $url,
                    'count' => $expireMinutes,
                    'notifiable' => $notifiable,
                ]);
        });
    }
}
