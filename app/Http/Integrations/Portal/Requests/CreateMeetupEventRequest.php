<?php

namespace App\Http\Integrations\Portal\Requests;

use Saloon\Enums\Method;

/**
 * POST /api/meetup-events — legt einen Termin für ein eigenes Meetup an.
 * meetup_id ist die ID des Meetups; start ist ein Datum/Uhrzeit-String.
 *
 * Payload-Shape:
 * array{
 *   meetup_id: int,
 *   start: string,
 *   location?: ?string,
 *   description?: ?string,
 *   link?: ?string,
 *   recurrence_type?: ?string,
 *   recurrence_day_of_week?: ?string,
 *   recurrence_day_position?: ?string,
 *   recurrence_interval?: ?int,
 *   recurrence_end_date?: ?string,
 * }
 */
class CreateMeetupEventRequest extends PortalWriteRequest
{
    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/meetup-events';
    }
}
