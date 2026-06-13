<?php

namespace App\Http\Integrations\Portal\Requests;

use Saloon\Enums\Method;

/**
 * POST /api/meetup — legt ein neues Meetup für den Token-Inhaber an
 * (created_by wird serverseitig gesetzt). Erwartet city_id als ID,
 * nicht den Stadtnamen.
 *
 * Payload-Shape:
 * array{
 *   name: string,
 *   city_id: int,
 *   intro?: ?string,
 *   telegram_link?: ?string,
 *   webpage?: ?string,
 *   twitter_username?: ?string,
 *   matrix_group?: ?string,
 *   nostr?: ?string,
 *   simplex?: ?string,
 *   signal?: ?string,
 *   community?: ?string,
 *   visible_on_map?: bool,
 *   is_active?: bool,
 * }
 */
class CreateMeetupRequest extends PortalWriteRequest
{
    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/meetup';
    }
}
