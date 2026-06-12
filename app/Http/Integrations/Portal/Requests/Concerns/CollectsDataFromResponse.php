<?php

namespace App\Http\Integrations\Portal\Requests\Concerns;

use Illuminate\Support\Collection;
use Saloon\Http\Response;
use Spatie\LaravelData\Data;

/**
 * Teilt das DTO-Mapping zwischen dem Saloon-Response-Pfad ($response->dto())
 * und dem Cache-Pfad in PortalApi, der rohes JSON ohne Response-Objekt mappt.
 *
 * @template TData of Data
 */
trait CollectsDataFromResponse
{
    /**
     * @param  array<int|string, mixed>  $json
     * @return Collection<int, TData>
     */
    abstract public static function collectData(array $json): Collection;

    /**
     * @return Collection<int, TData>
     */
    public function createDtoFromResponse(Response $response): Collection
    {
        return static::collectData($response->json());
    }
}
