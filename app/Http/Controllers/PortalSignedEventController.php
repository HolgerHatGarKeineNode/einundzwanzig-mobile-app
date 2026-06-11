<?php

namespace App\Http\Controllers;

use App\Services\PortalAuth;
use Illuminate\Http\RedirectResponse;

/**
 * Receives the NIP-55 signer callback via the custom scheme
 * einundzwanzig://signed/{k1}/{event}. The signer (e.g. Amber) opens this
 * deep link directly after signing, so the token exchange happens entirely
 * in the app — no browser handoff page, no Custom Tab. Trades the signed
 * event for a Sanctum token at the portal and stores it.
 */
final class PortalSignedEventController extends Controller
{
    public function __invoke(string $payload, PortalAuth $portalAuth): RedirectResponse
    {
        $k1 = substr($payload, 0, 64);

        if (strlen($payload) > 64 && ctype_xdigit($k1)) {
            $signedEvent = json_decode(ltrim(substr($payload, 64), '/'), true);

            if (is_array($signedEvent) && $portalAuth->exchangeSignedEvent($k1, $signedEvent)) {
                return redirect()->route('home')->with('portal-connected', true);
            }
        }

        return redirect()->route('home')->with('portal-connect-failed', true);
    }
}
