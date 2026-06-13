<?php

namespace App\Http\Integrations\Portal\Requests;

use Saloon\Enums\Method;

/**
 * PATCH /api/meetup-events/{id} — aktualisiert einen eigenen Termin.
 * Teil-Payload genügt („sometimes"). Nur Ersteller/Super-Admin (403).
 *
 * Payload-Shape: wie CreateMeetupEventRequest, alle Felder optional.
 */
class UpdateMeetupEventRequest extends PortalWriteRequest
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
        return "/meetup-events/{$this->id}";
    }
}
