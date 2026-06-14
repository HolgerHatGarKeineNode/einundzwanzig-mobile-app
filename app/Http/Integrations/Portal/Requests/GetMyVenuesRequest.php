<?php

namespace App\Http\Integrations\Portal\Requests;

use App\Data\Portal\MyVenueData;
use Illuminate\Support\Collection;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * GET /api/my-venues — vom Nutzer ERSTELLTE Veranstaltungsorte
 * (auth:sanctum). Die Antwort ist eine Resource-Collection mit data-Wrapper.
 */
class GetMyVenuesRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/my-venues';
    }

    /**
     * @param  array<int|string, mixed>  $json
     * @return Collection<int, MyVenueData>
     */
    public static function collectData(array $json): Collection
    {
        return MyVenueData::collect($json, Collection::class);
    }

    /**
     * @return Collection<int, MyVenueData>
     */
    public function createDtoFromResponse(Response $response): Collection
    {
        return static::collectData($response->json('data') ?? []);
    }
}
