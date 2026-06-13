<?php

namespace App\Http\Integrations\Portal\Requests;

use Saloon\Enums\Method;

/**
 * PATCH /api/meetup/{id} — aktualisiert ein eigenes Meetup. Die API
 * validiert mit „sometimes", daher genügt eine Teil-Payload (nur die
 * geänderten Felder). Nur Ersteller/Super-Admin dürfen ändern (403).
 *
 * Payload-Shape: wie CreateMeetupRequest, alle Felder optional.
 */
class UpdateMeetupRequest extends PortalWriteRequest
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
        return "/meetup/{$this->id}";
    }
}
