<?php

namespace App\Http\Integrations\Portal\Requests;

use App\Data\Portal\MyCityData;
use Illuminate\Support\Collection;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * GET /api/my-cities — vom Nutzer ERSTELLTE Städte (auth:sanctum).
 * Die Antwort ist eine Resource-Collection mit data-Wrapper.
 */
class GetMyCitiesRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/my-cities';
    }

    /**
     * @param  array<int|string, mixed>  $json
     * @return Collection<int, MyCityData>
     */
    public static function collectData(array $json): Collection
    {
        return MyCityData::collect($json, Collection::class);
    }

    /**
     * @return Collection<int, MyCityData>
     */
    public function createDtoFromResponse(Response $response): Collection
    {
        return static::collectData($response->json('data') ?? []);
    }
}
