<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Native\Mobile\Facades\SecureStorage;

/**
 * Manages the Sanctum personal access token issued by the
 * Einundzwanzig portal and stored in the device keystore.
 */
final class PortalAuth
{
    private const TOKEN_KEY = 'portal_api_token';

    public function baseUrl(): string
    {
        return rtrim((string) config('services.portal.url'), '/');
    }

    /**
     * URL of the portal's mobile login page. The portal redirects back via
     * the einundzwanzig://auth deep link carrying the token.
     */
    public function loginUrl(): string
    {
        return $this->baseUrl().'/auth/mobile?'.http_build_query([
            'redirect_uri' => 'einundzwanzig://auth',
            'device_name' => $this->deviceName(),
        ]);
    }

    public function deviceName(): string
    {
        return 'Einundzwanzig App (Android)';
    }

    /**
     * Build the NIP-55 signer URI for a fresh login challenge.
     *
     * Launching this via an ACTION_VIEW intent (Browser::open) opens the
     * NIP-55 signer (e.g. Amber) directly — no portal page in between. The
     * signer signs the kind-22242 event locally and opens the callback URL
     * (the challenge travels in its path, the signed event is appended), so
     * verification is stateless and needs no relay round-trip.
     */
    public function nostrSignerUri(string $k1): string
    {
        $event = [
            'kind' => 22242,
            'created_at' => now()->timestamp,
            'content' => '',
            'tags' => [['challenge', $k1]],
        ];

        // Amber strips query strings when it rebuilds the callback URL, so
        // the k1 travels in the path; the signer appends the signed event
        // after the trailing slash.
        $callbackUrl = $this->baseUrl().'/auth/mobile/signed/'.$k1.'/';

        return 'nostrsigner:'.rawurlencode(json_encode($event)).'?'.http_build_query([
            'compressionType' => 'none',
            'returnType' => 'event',
            'type' => 'sign_event',
            'appName' => 'Einundzwanzig',
            'callbackUrl' => $callbackUrl,
        ]);
    }

    public function newChallenge(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function storeToken(string $token): bool
    {
        return SecureStorage::set(self::TOKEN_KEY, $token);
    }

    public function token(): ?string
    {
        return SecureStorage::get(self::TOKEN_KEY);
    }

    public function hasToken(): bool
    {
        return $this->token() !== null;
    }

    public function forgetToken(): bool
    {
        return SecureStorage::delete(self::TOKEN_KEY);
    }

    /**
     * Fetch the token owner's profile from the portal. Returns null when
     * offline or not authenticated; a 401 discards the stored token.
     *
     * @return array{id: int, name: string, email: string|null, nostr: string|null, is_lecturer: bool, is_leader: bool, avatar: string|null}|null
     */
    public function profile(): ?array
    {
        $token = $this->token();

        if ($token === null) {
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->withToken($token)
                ->acceptJson()
                ->get($this->baseUrl().'/api/user');
        } catch (ConnectionException) {
            return null;
        }

        if ($response->unauthorized()) {
            $this->forgetToken();

            return null;
        }

        return $response->successful() ? $response->json() : null;
    }
}
