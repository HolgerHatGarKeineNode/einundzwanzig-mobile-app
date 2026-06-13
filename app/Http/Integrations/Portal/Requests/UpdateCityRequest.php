<?php

namespace App\Http\Integrations\Portal\Requests;

use Saloon\Enums\Method;

/**
 * PATCH /api/cities/{id} — aktualisiert eine eigene Stadt.
 * Teil-Payload genügt („sometimes"). Nur Ersteller/Super-Admin (403).
 *
 * Payload-Shape: wie CreateCityRequest, alle Felder optional.
 */
class UpdateCityRequest extends PortalWriteRequest
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
        return "/cities/{$this->id}";
    }
}
