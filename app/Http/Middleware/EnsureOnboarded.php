<?php

namespace App\Http\Middleware;

use App\Services\AppPreferences;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wendet die gespeicherte App-Sprache an und leitet auf das Onboarding um,
 * solange es noch nicht abgeschlossen ist. Nur auf den Seiten-Routen
 * registriert — die Deep-Link-/Auth-Callback-Routen (einundzwanzig://…)
 * dürfen niemals umgeleitet werden.
 */
class EnsureOnboarded
{
    public function __construct(private readonly AppPreferences $preferences) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->preferences->isOnboarded()) {
            return redirect()->route('onboarding');
        }

        app()->setLocale($this->preferences->locale());

        return $next($request);
    }
}
