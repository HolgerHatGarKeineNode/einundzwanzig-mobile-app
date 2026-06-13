<?php

namespace App\Http\Integrations\Portal\Requests;

use Saloon\Enums\Method;

/**
 * POST /api/venues — legt einen Veranstaltungsort an. city_id ist die
 * ID der Stadt, street ist Pflicht.
 *
 * Payload-Shape:
 * array{
 *   city_id: int,
 *   name: string,
 *   street: string,
 * }
 */
class CreateVenueRequest extends PortalWriteRequest
{
    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/venues';
    }
}
