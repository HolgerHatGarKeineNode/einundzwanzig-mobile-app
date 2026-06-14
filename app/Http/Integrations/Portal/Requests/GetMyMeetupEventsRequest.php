<?php

namespace App\Http\Integrations\Portal\Requests;

use App\Data\Portal\MyMeetupEventData;
use Illuminate\Support\Collection;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * GET /api/my-meetup-events — vom Nutzer ERSTELLTE Meetup-Termine
 * (auth:sanctum), nach Startzeit absteigend. Die Antwort ist eine
 * Resource-Collection mit data-Wrapper. Es gibt portalseitig keinen
 * meetup_id-Filter — die Zuordnung passiert in der App über meetup_id.
 */
class GetMyMeetupEventsRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/my-meetup-events';
    }

    /**
     * @param  array<int|string, mixed>  $json
     * @return Collection<int, MyMeetupEventData>
     */
    public static function collectData(array $json): Collection
    {
        return MyMeetupEventData::collect($json, Collection::class);
    }

    /**
     * @return Collection<int, MyMeetupEventData>
     */
    public function createDtoFromResponse(Response $response): Collection
    {
        return static::collectData($response->json('data') ?? []);
    }
}
