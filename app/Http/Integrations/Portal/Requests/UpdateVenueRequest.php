<?php

namespace App\Http\Integrations\Portal\Requests;

use Saloon\Enums\Method;

/**
 * PATCH /api/venues/{id} — aktualisiert einen eigenen Veranstaltungsort.
 * Teil-Payload genügt („sometimes"). Nur Ersteller/Super-Admin (403).
 *
 * Payload-Shape: wie CreateVenueRequest, alle Felder optional.
 */
class UpdateVenueRequest extends PortalWriteRequest
{
    protected Method $method = Method::PATCH;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(private readonly int $id, array $payload)
    {
        parent::__construct($payload);
    }

    public function resolveEndpoint(): string
    {
        return "/venues/{$this->id}";
    }
}
