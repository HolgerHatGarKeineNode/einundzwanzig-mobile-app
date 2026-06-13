<?php

namespace App\Http\Integrations\Portal\Requests;

use Saloon\Enums\Method;

/**
 * POST /api/cities — legt eine Stadt an. country_id ist die ID des
 * Landes; longitude und latitude sind Pflicht (Karten-Picker).
 *
 * Payload-Shape:
 * array{
 *   country_id: int,
 *   name: string,
 *   longitude: float,
 *   latitude: float,
 *   population?: ?int,
 * }
 */
class CreateCityRequest extends PortalWriteRequest
{
    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/cities';
    }
}
