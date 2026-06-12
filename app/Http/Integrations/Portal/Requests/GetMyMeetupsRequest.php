<?php

namespace App\Http\Integrations\Portal\Requests;

use App\Data\Portal\MeetupData;
use Illuminate\Support\Collection;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * GET /api/my-meetups — vom Nutzer ERSTELLTE Meetups (auth:sanctum).
 * Die Antwort ist eine Resource-Collection mit data-Wrapper.
 */
class GetMyMeetupsRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/my-meetups';
    }

    /**
     * @param  array<int|string, mixed>  $json
     * @return Collection<int, MeetupData>
     */
    public static function collectData(array $json): Collection
    {
        return MeetupData::collect($json, Collection::class);
    }

    /**
     * @return Collection<int, MeetupData>
     */
    public function createDtoFromResponse(Response $response): Collection
    {
        return static::collectData($response->json('data') ?? []);
    }
}
