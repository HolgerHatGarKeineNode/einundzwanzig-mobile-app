<?php

namespace App\Http\Integrations\Portal\Requests;

use App\Data\Portal\CountryData;
use App\Http\Integrations\Portal\Requests\Concerns\CollectsDataFromResponse;
use Illuminate\Support\Collection;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/countries — öffentliche Länder-Liste (id, name, code, flag).
 * Ohne selected begrenzt das Portal auf 10 Einträge; selected akzeptiert
 * Codes oder IDs.
 */
class GetCountriesRequest extends Request
{
    /** @use CollectsDataFromResponse<CountryData> */
    use CollectsDataFromResponse;

    protected Method $method = Method::GET;

    /**
     * @param  list<int|string>  $selected
     */
    public function __construct(
        private readonly ?string $search = null,
        private readonly array $selected = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return '/countries';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return array_filter([
            'search' => $this->search,
            'selected' => $this->selected !== [] ? $this->selected : null,
        ]);
    }

    /**
     * @param  array<int|string, mixed>  $json
     * @return Collection<int, CountryData>
     */
    public static function collectData(array $json): Collection
    {
        return CountryData::collect($json, Collection::class);
    }
}
