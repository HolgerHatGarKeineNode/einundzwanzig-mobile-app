<?php

namespace App\Http\Integrations\Portal\Requests;

use App\Data\Portal\CityData;
use App\Http\Integrations\Portal\Requests\Concerns\CollectsDataFromResponse;
use Illuminate\Support\Collection;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/cities — öffentliche Städte-Liste inkl. Land.
 * Ohne selected/withDetails begrenzt das Portal auf 10 Einträge;
 * das Presence-Flag withDetails hebt das Limit auf und liefert
 * zusätzlich country.code und die Flaggen-URL.
 */
class GetCitiesRequest extends Request
{
    /** @use CollectsDataFromResponse<CityData> */
    use CollectsDataFromResponse;

    protected Method $method = Method::GET;

    /**
     * @param  list<int>  $selected
     */
    public function __construct(
        private readonly ?string $search = null,
        private readonly array $selected = [],
        private readonly bool $withDetails = false,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/cities';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return array_filter([
            'search' => $this->search,
            'selected' => $this->selected !== [] ? $this->selected : null,
            'withDetails' => $this->withDetails ? '1' : null,
        ]);
    }

    /**
     * @param  array<int|string, mixed>  $json
     * @return Collection<int, CityData>
     */
    public static function collectData(array $json): Collection
    {
        return CityData::collect($json, Collection::class);
    }
}
