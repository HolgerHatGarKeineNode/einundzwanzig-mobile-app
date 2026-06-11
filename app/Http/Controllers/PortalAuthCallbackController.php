<?php

namespace App\Http\Controllers;

use App\Services\PortalAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Receives the einundzwanzig://auth deep link from the portal login flow.
 * The native shell maps it to GET /auth?token=… on the embedded Laravel.
 */
final class PortalAuthCallbackController extends Controller
{
    public function __invoke(Request $request, PortalAuth $portalAuth): RedirectResponse
    {
        $token = (string) $request->query('token', '');

        if ($token !== '') {
            $portalAuth->storeToken($token);

            return redirect()->route('home')->with('portal-connected', true);
        }

        return redirect()->route('home');
    }
}
