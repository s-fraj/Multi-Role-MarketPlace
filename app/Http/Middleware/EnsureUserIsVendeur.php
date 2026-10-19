<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVendeur
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isVendeur()) {
            abort(403, "Cette page est réservée aux comptes vendeur.");
        }

        return $next($request);
    }
}
