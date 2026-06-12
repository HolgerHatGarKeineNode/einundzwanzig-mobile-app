<?php

namespace App\Http\Integrations\Portal\Requests;

use App\Data\Portal\VenueData;
use App\Http\Integrations\Portal\Requests\Concerns\CollectsDataFromResponse;
use Illuminate\Support\Collection;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/venues — öffentliche Veranstaltungsorte inkl. Stadt/Land,
 * Flaggen-URL und Beschreibung. Ohne selected begrenzt auf 10 Einträge.
 */
class GetVenuesRequest extends Request
{
    /** @use CollectsDataFromResponse<VenueData> */
    use CollectsDataFromResponse;

    protected Method $method = Method::GET;

    /**
     * @param  list<int>  $selected
     */
    public function __construct(
        private readonly ?string $search = null,
        private readonly array $selected = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return '/venues';
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
     * @return Collection<int, VenueData>
     */
    public static function collectData(array $json): Collection
    {
        return VenueData::collect($json, Collection::class);
    }
}
